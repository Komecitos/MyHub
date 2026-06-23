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
        $completedSessions = FreefireSpinSession::whereIn('spin_type', ['token_ring', 'token_tower'])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        $completedTokenSessions = $completedSessions->where('spin_type', 'token_ring');
        $completedTowerSessions = $completedSessions->where('spin_type', 'token_tower');

        return view('freefire::calc', compact('completedTokenSessions', 'completedTowerSessions'));
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
                } else if ($session->spin_type === 'faded_wheel') {
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
                } else {
                    if ($session->spin_type === 'faded_wheel') {
                        $session->luck_actual = 50;
                        $session->remaining_token = 0;
                        $session->est_diamond_left = 0;
                        $session->est_spins_left = 0;
                        $session->expected_token_per_spin = 0;

                        $session->next_spin_cost = isset($fadedPrices[$session->current_spin])
                            ? round($fadedPrices[$session->current_spin] * (1 - $session->discount_percentage / 100))
                            : 0;

                        $remainingFadedCost = 0;
                        for ($i = $session->current_spin; $i < 8; $i++) {
                            $remainingFadedCost += round($fadedPrices[$i] * (1 - $session->discount_percentage / 100));
                        }
                        $session->remaining_faded_cost = $remainingFadedCost;
                    } else {
                        // Token Tower
                        $towerPity = [20, 35, 50, 80, 100];
                        $currentTokenLevel = $session->current_token; // 0-5
                        $remaining = max(0, 5 - $currentTokenLevel);

                        $remainingPitySpins = 0;
                        for ($i = $currentTokenLevel; $i < 5; $i++) {
                            $remainingPitySpins += $towerPity[$i];
                        }

                        $session->remaining_token = $remaining;
                        $session->est_spins_left = $remainingPitySpins;

                        $fiveSpins = floor($remainingPitySpins / 5);
                        $oneSpins = $remainingPitySpins % 5;
                        $session->est_diamond_left = ($fiveSpins * 79) + ($oneSpins * 19);

                        $session->luck_actual = 0;
                        $session->next_spin_cost = 0;
                        $session->remaining_faded_cost = 0;
                        $session->expected_token_per_spin = 0;
                    }
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
            'spin_type'           => 'required|in:token_ring,faded_wheel,token_tower',
            'token_needed'        => 'nullable|integer|min:1',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'event_start'         => 'nullable|date',
            'event_end'           => 'nullable|date|after_or_equal:event_start',
            'slots'               => 'nullable|array',
        ]);

        $session = FreefireSpinSession::create([
            'item_name'           => $request->item_name,
            'spin_type'           => $request->spin_type,
            'token_needed'        => $request->spin_type === 'token_tower' ? 5 : $request->token_needed,
            'luck_percentage'     => 0,
            'discount_percentage' => ($request->price_mode === 'discount' || $request->tower_price_mode === 'discount') ? 1 : 0,
            'modal_diamond'       => 0,
            'spent_diamond'       => 0,
            'current_spin'        => 0,
            'current_token'       => 0,
            'status'              => 'active',
            'event_start'         => $request->event_start,
            'event_end'           => $request->event_end,
            'starting_token'      => $request->starting_token ?? 0,
            'ticket_count'        => $request->ticket_count ?? 0,
        ]);

        if ($request->slots) {
            foreach ($request->slots as $slot) {
                $slotCount = intval($slot['slot_count'] ?? 0);
                if ($slotCount === 0) continue;

                FreefireWheelSlot::create([
                    'session_id'  => $session->id,
                    'type'        => $slot['type'],
                    'token_value' => ($slot['type'] === 'token') ? $slot['token_value'] : null,
                    'item_name'   => ($slot['type'] === 'item') ? ($slot['item_name'] ?? null) : null,
                    'rarity'      => $slot['rarity'] ?? null,
                    'slot_count'  => $slotCount,
                ]);
            }
        }

        return redirect()->route('freefire.session')->with('success', 'Sesi spin baru dibuat!');
    }

    public function addLog(Request $request, $id)
    {
        $session = FreefireSpinSession::findOrFail($id);

        $request->validate([
            'spin_count'         => 'required|integer|min:1',
            'diamond_spent'      => 'required|integer|min:0',
            'token_gained'       => 'nullable|integer|min:0',
            'tower_token_number' => 'nullable|integer|min:1|max:5',
            'got_item_id'        => 'nullable|array',
        ]);

        $session->current_spin += $request->spin_count;
        $session->spent_diamond += $request->diamond_spent;

        $resultParts = [];

        if ($session->spin_type === 'token_tower') {
            // Token Tower: naik level berdasarkan checkbox
            if ($request->tower_token_number) {
                $session->current_token = max($session->current_token, $request->tower_token_number);
                $resultParts[] = 'Naik ke Token ' . $request->tower_token_number;
            }
        } else {
            // Token Ring: akumulasi token bebas
            $session->current_token += $request->token_gained ?? 0;
            if ($request->token_gained > 0) {
                $resultParts[] = 'Token +' . $request->token_gained;
            }
        }

        $session->save();

        // Item langsung yang didapat (khusus Token Ring, via checkbox slot item)
        if ($request->got_item_id && is_array($request->got_item_id)) {
            $obtainedNames = [];

            foreach ($request->got_item_id as $slotId) {
                $slot = FreefireWheelSlot::find($slotId);
                if ($slot && $slot->type === 'item') {
                    $obtainedNames[] = $slot->item_name;
                }
            }

            if (!empty($obtainedNames)) {
                $existing = $session->obtained_items ?? [];
                $session->obtained_items = array_unique(array_merge($existing, $obtainedNames));
                $session->save();

                $resultParts[] = 'Dapat item: ' . implode(', ', $obtainedNames);
            }
        }

        FreefireSpinLog::create([
            'session_id'    => $session->id,
            'spin_number'   => $session->current_spin,
            'diamond_spent' => $request->diamond_spent,
            'result'        => !empty($resultParts) ? implode(' · ', $resultParts) : null,
            'token_gained'  => $request->token_gained ?? 0,
        ]);

        // Auto-complete Token Tower kalau sudah dapat token 5
        if ($session->spin_type === 'token_tower' && $session->current_token >= 5) {
            $session->update(['status' => 'completed']);
            return redirect()->route('freefire.session')->with('success', 'Selamat! Bundle utama berhasil didapat! 🎉');
        }

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
