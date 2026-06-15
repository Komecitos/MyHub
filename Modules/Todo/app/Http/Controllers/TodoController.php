<?php

namespace Modules\Todo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Todo\Models\Todo;
use Modules\Todo\Models\TodoLog;

class TodoController extends Controller
{
    private function getDayCode($date)
    {
        return match ($date->dayOfWeek) {
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
            0 => 'sun',
        };
    }
    public function index()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $todayDay = $this->getDayCode(now());

        $todayTasks = Todo::where('status', '!=', 'completed')
            ->get()
            ->filter(function ($todo) use ($today, $todayDay) {

                // common tasks
                if (!$todo->is_recurring) {
                    return $todo->due_date === $today;
                }

                // Recurring weekly
                if (
                    $todo->recur_type === 'weekly' &&
                    is_array($todo->recur_days)
                ) {
                    if ($todo->due_date > $today) {
                        return false;
                    }

                    return in_array($todayDay, $todo->recur_days);
                }
            });

        $tomorrowTasks = Todo::where('status', '!=', 'completed')
            ->whereDate('due_date', $tomorrow)
            ->orderBy('priority', 'desc')
            ->get();

        $weekTasks = Todo::where('status', '!=', 'completed')
            ->whereDate('due_date', '>', $tomorrow)
            ->whereDate('due_date', '<=', $weekEnd)
            ->orderBy('due_date')
            ->get();

        $upcomingTasks = Todo::where('status', '!=', 'completed')
            ->whereDate('due_date', '>', $weekEnd)
            ->orderBy('due_date')
            ->get();

        $allTasks = Todo::where('status', '!=', 'completed')->get();

        $noDueDateTasks = Todo::where('status', '!=', 'completed')
            ->whereNull('due_date')
            ->orderBy('priority', 'desc')
            ->get();

        $completedTasks = Todo::where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(20)
            ->get();

        $overdueTasks = Todo::where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date', 'asc')
            ->get();

        return view('todo::index', compact(
            'todayTasks',
            'overdueTasks',
            'tomorrowTasks',
            'weekTasks',
            'upcomingTasks',
            'noDueDateTasks',
            'completedTasks',
            'allTasks'
        ));
    }

    public function create()
    {
        return view('todo::create');
    }

    public function store(Request $request)
    {

        $isRecurring = $request->boolean('is_recurring');

        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:pending,in_progress,completed',
            'priority'       => 'required|in:low,medium,high',
            'category'       => 'nullable|string|max:100',
            'due_date'       => $isRecurring ? 'nullable' : 'required|date',
            'due_time'       => 'nullable|date_format:H:i',
            'is_recurring'   => 'nullable|boolean',
            'recur_type'     => 'required_if:is_recurring,1|nullable|in:daily,weekly,monthly',
            'recur_interval' => 'required_if:is_recurring,1|nullable|integer|min:1',
            'recur_days'     => 'nullable|array',
        ], [
            'title.required'             => 'Judul tugas wajib diisi.',
            'due_date.required'          => 'Tanggal deadline wajib diisi.',
            'status.required'            => 'Status wajib dipilih.',
            'priority.required'          => 'Prioritas wajib dipilih.',
            'recur_type.required_if'     => 'Tipe pengulangan wajib dipilih jika recurring.',
            'recur_interval.required_if' => 'Interval pengulangan wajib diisi jika recurring.',
        ]);

        $dueDate = $isRecurring ? null : $request->due_date;

        $todo = Todo::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'status'         => $request->status,
            'priority'       => $request->priority,
            'category'       => $request->category,
            'due_date'       => $dueDate,
            'due_time'       => $request->due_time,
            'is_recurring'   => $request->boolean('is_recurring'),
            'recur_type'     => $request->recur_type,
            'recur_interval' => $request->recur_interval ?? 1,
            'recur_days' => $request->recur_days,
            'parent_id'      => null,
            'completed_at'   => null,
        ]);

        TodoLog::create([
            'todo_id'     => $todo->id,
            'action'      => 'created',
            'todo_title'  => $todo->title,
            'description' => 'Tugas dibuat dengan status ' . $todo->status . ' · prioritas ' . $todo->priority,
        ]);

        return redirect()->route('todo.index')->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function show($id)
    {
        $todo = Todo::findOrFail($id);
        return view('todo::show', compact('todo'));
    }

    public function edit($id)
    {
        $todo = Todo::findOrFail($id);
        return view('todo::edit', compact('todo'));
    }

    public function update(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);

        $request->validate([
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:pending,in_progress,completed',
            'priority'       => 'required|in:low,medium,high',
            'category'       => 'nullable|string|max:100',
            'due_date'       => 'nullable|date',
            'due_time'       => 'nullable',
            'is_recurring'   => 'boolean',
            'recur_type'     => 'nullable|in:daily,weekly,monthly',
            'recur_interval' => 'nullable|integer|min:1',
            'recur_days'     => 'nullable|array',
        ]);

        $oldStatus = $todo->status;
        $newStatus = $request->status;

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $request->merge(['completed_at' => now()]);

            if ($todo->is_recurring) {
                $this->createNextRecurring($todo);
            }

            TodoLog::create([
                'todo_id'     => $todo->id,
                'action'      => 'completed',
                'todo_title'  => $todo->title,
                'description' => 'Tugas ditandai selesai.',
            ]);
        } elseif ($oldStatus === 'completed' && $newStatus !== 'completed') {
            $request->merge(['completed_at' => null]);

            TodoLog::create([
                'todo_id'     => $todo->id,
                'action'      => 'recovered',
                'todo_title'  => $todo->title,
                'description' => 'Tugas dikembalikan ke ' . $newStatus . '.',
            ]);
        } else {
            TodoLog::create([
                'todo_id'     => $todo->id,
                'action'      => 'updated',
                'todo_title'  => $todo->title,
                'description' => 'Tugas diedit.',
            ]);
        }

        $todo->update([
            'title'          => $request->title,
            'description'    => $request->description,
            'status'         => $request->status,
            'priority'       => $request->priority,
            'category'       => $request->category,
            'due_date'       => $request->due_date,
            'due_time'       => $request->due_time,
            'is_recurring'   => $request->boolean('is_recurring'),
            'recur_type'     => $request->recur_type,
            'recur_interval' => $request->recur_interval,
            'recur_days'     => $request->recur_days,
            'completed_at'   => $request->completed_at ?? $todo->completed_at,
        ]);

        return redirect()->route('todo.index')->with('success', 'Tugas berhasil diupdate!');
    }

    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);

        TodoLog::create([
            'todo_id'     => $todo->id,
            'action'      => 'deleted',
            'todo_title'  => $todo->title,
            'description' => 'Tugas dihapus permanen.',
        ]);

        $todo->delete();

        return redirect()->route('todo.index')->with('success', 'Tugas berhasil dihapus!');
    }

    private function createNextRecurring(Todo $todo)
    {
        $nextDate = match ($todo->recur_type) {
            'daily'   => now()->addDays($todo->recur_interval),
            'weekly'  => now()->addWeeks($todo->recur_interval),
            'monthly' => now()->addMonths($todo->recur_interval),
            default   => null,
        };

        if ($nextDate) {
            Todo::create([
                'title'          => $todo->title,
                'description'    => $todo->description,
                'status'         => 'pending',
                'priority'       => $todo->priority,
                'category'       => $todo->category,
                'due_date'       => $nextDate->toDateString(),
                'due_time'       => $todo->due_time,
                'is_recurring'   => true,
                'recur_type'     => $todo->recur_type,
                'recur_interval' => $todo->recur_interval,
                'parent_id'      => $todo->id,
            ]);
        }
    }

    public function history()
    {
        $logs = TodoLog::when(request('action'), function ($query) {
            $query->where('action', request('action'));
        })
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('todo::history', compact('logs'));
    }
}
