<?php

namespace Modules\FreeFire\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FreeFire\Models\FreefireSpinSession;
use Modules\FreeFire\Models\FreefireSpinLog;
use Modules\FreeFire\Models\FreefireWheelSlot;
use Modules\FreeFire\Services\FreefireWheelCalculator;

class FreeFireController extends Controller
{
    public function index()
    {
        return redirect()->route('freefire.calc');
    }

    public function calc()
    {
        $completedTokenSessions = FreefireSpinSession::where('status', 'completed')
            ->where('spin_type', 'token_ring')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Hitung rata-rata token per spin dari SEMUA riwayat (untuk koreksi, kurangi starting_token agar akurat)
        $totalToken = $completedTokenSessions->map(function ($s) {
            return max(0, $s->current_token - ($s->starting_token ?? 0));
        })->sum();
        $totalSpin = $completedTokenSessions->sum('current_spin');
        $avgActualTokenPerSpin = $totalSpin > 0 ? round($totalToken / $totalSpin, 2) : null;

        return view('freefire::calc', compact('completedTokenSessions', 'avgActualTokenPerSpin'));
    }
    public function session()
    {
        // Auto-complete expired sessions
        FreefireSpinSession::where('status', 'active')
            ->whereNotNull('event_end')
            ->whereDate('event_end', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        $activeSessions = FreefireSpinSession::where('status', 'active')
            ->with(['slots', 'logs'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($session) {
                // Kumpulkan item yang sudah didapat langsung dari log
                $session->obtained_items = $session->logs
                    ->filter(fn($log) => !is_null($log->result) && str_starts_with($log->result, 'Item: '))
                    ->map(fn($log) => ltrim(substr($log->result, 6)))
                    ->values();

                if ($session->spin_type === 'token_ring') {
                    $estimates = FreefireWheelCalculator::buildSessionEstimates($session);

                    $session->token_target = FreefireWheelCalculator::resolveTargetToken($session);
                    $session->expected_token_per_spin = $estimates['expected_token_per_spin'];
                    $session->avg_token_per_spin = $estimates['avg_token_per_spin'];
                    $session->luck_actual = $estimates['luck_actual'];
                    $session->item_estimates = $estimates['items'];
                    $session->next_spin_cost = 0;
                    $session->remaining_faded_cost = 0;
                    $session->remaining_token = $session->token_target !== null
                        ? max(0, $session->token_target - $session->current_token)
                        : 0;
                } else {
                    $session->luck_actual = 50;
                    $session->remaining_token = 0;
                    $session->est_diamond_left = 0;
                    $session->est_spins_left = 0;
                    $session->expected_token_per_spin = 0;

                    $session->next_spin_cost = FreefireWheelCalculator::fadedPrice(
                        $session->current_spin,
                        $session->discount_percentage
                    );

                    $session->remaining_faded_cost = FreefireWheelCalculator::remainingFadedCost(
                        $session->current_spin,
                        $session->discount_percentage
                    );
                }

                return $session;
            });

        $completedSessions = FreefireSpinSession::where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('freefire::session', compact('activeSessions', 'completedSessions'));
    }

    public function storeSession(Request $request)
    {
        $request->validate([
            'item_name'           => 'required|string|max:255',
            'spin_type'           => 'required|in:token_ring,faded_wheel',
            'starting_token'      => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'event_start'         => 'nullable|date',
            'event_end'           => 'nullable|date|after_or_equal:event_start',
            'slots'               => 'nullable|array',
            'slots.*.type'        => 'nullable|in:token,item',
            'slots.*.token_value' => 'nullable|integer',
            'slots.*.item_name'      => 'nullable|string',
            'slots.*.token_exchange' => 'nullable|integer|min:1',
            'slots.*.rarity'         => 'nullable|in:epic,legendary,artifact',
            'slots.*.slot_count'     => 'nullable|integer|min:0',
        ]);

        $discountPercentage = $request->spin_type === 'faded_wheel' && $request->boolean('has_discount')
            ? 20
            : 0;

        $startingToken = $request->spin_type === 'token_ring' ? intval($request->input('starting_token', 0)) : 0;

        $session = FreefireSpinSession::create([
            'item_name'           => $request->item_name,
            'spin_type'           => $request->spin_type,
            'token_needed'        => null,
            'starting_token'      => $startingToken,
            'luck_percentage'     => 50,
            'discount_percentage' => $discountPercentage,
            'modal_diamond'       => 0,
            'spent_diamond'       => 0,
            'current_spin'        => 0,
            'current_token'       => $startingToken,
            'status'              => 'active',
            'event_start'         => $request->event_start,
            'event_end'           => $request->event_end,
        ]);

        // Simpan wheel slots
        if ($request->slots) {
            foreach ($request->slots as $slot) {
                $slotCount = intval($slot['slot_count'] ?? 0);
                if ($slotCount === 0) continue; // skip slot kosong

                FreefireWheelSlot::create([
                    'session_id'      => $session->id,
                    'type'            => $slot['type'],
                    'token_value'     => ($slot['type'] === 'token') ? $slot['token_value'] : null,
                    'item_name'       => ($slot['type'] === 'item') ? ($slot['item_name'] ?? null) : null,
                    'token_exchange'  => ($slot['type'] === 'item') ? ($slot['token_exchange'] ?? null) : null,
                    'rarity'          => $slot['rarity'] ?? null,
                    'slot_count'      => $slotCount,
                ]);
            }
        }

        return redirect()->route('freefire.session')->with('success', 'Sesi spin baru dibuat!');
    }

    public function addLog(Request $request, $id)
    {
        $session = FreefireSpinSession::findOrFail($id);

        $request->validate([
            'spin_count'      => 'required|integer|min:1',
            'diamond_spent'   => 'required|integer|min:0',
            'token_gained'    => 'nullable|integer|min:0',
            'got_item_id'     => 'nullable|array',
            'got_item_id.*'   => 'integer|exists:freefire_wheel_slots,id',
        ]);

        $gotItemSlot = null;
        if ($request->filled('got_item_id')) {
            $gotItemSlot = FreefireWheelSlot::where('session_id', $session->id)
                ->where('type', 'item')
                ->whereIn('id', $request->got_item_id)
                ->first();
        }

        $session->current_spin += $request->spin_count;
        $session->spent_diamond += $request->diamond_spent;
        $session->current_token += $request->token_gained ?? 0;
        $session->save();

        FreefireSpinLog::create([
            'session_id'    => $session->id,
            'spin_number'   => $session->current_spin,
            'diamond_spent' => $request->diamond_spent,
            'result'        => $gotItemSlot ? ('Item: ' . $gotItemSlot->item_name) : null,
            'token_gained'  => $request->token_gained ?? 0,
        ]);

        return redirect()->route('freefire.session')->with('success', 'Spin dicatat!');
    }

    public function completeSession($id)
    {
        $session = FreefireSpinSession::findOrFail($id);
        $session->update(['status' => 'completed']);

        return redirect()->route('freefire.session')->with('success', 'Sesi selesai!');
    }

    public function destroy($id)
    {
        $session = FreefireSpinSession::findOrFail($id);
        $session->delete();

        return redirect()->route('freefire.session')->with('success', 'Sesi dihapus!');
    }
}
