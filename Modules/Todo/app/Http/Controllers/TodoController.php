<?php

namespace Modules\Todo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Todo\Models\Todo;

class TodoController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $todayTasks = Todo::where('status', '!=', 'completed')
            ->whereDate('due_date', $today)
            ->orderBy('priority', 'desc')
            ->get();

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

        return view('todo::index', compact(
            'todayTasks',
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
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:pending,in_progress,completed',
            'priority'       => 'required|in:low,medium,high',
            'category'       => 'nullable|string|max:100',
            'due_date'       => 'required|date',
            'due_time'       => 'nullable|date_format:H:i',
            'is_recurring'   => 'nullable|boolean',
            'recur_type'     => 'required_if:is_recurring,1|nullable|in:daily,weekly,monthly',
            'recur_interval' => 'required_if:is_recurring,1|nullable|integer|min:1',
        ], [
            'title.required'          => 'Judul tugas wajib diisi.',
            'due_date.required'       => 'Tanggal deadline wajib diisi.',
            'status.required'         => 'Status wajib dipilih.',
            'priority.required'       => 'Prioritas wajib dipilih.',
            'recur_type.required_if'  => 'Tipe pengulangan wajib dipilih jika recurring.',
            'recur_interval.required_if' => 'Interval pengulangan wajib diisi jika recurring.',
        ]);

        Todo::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'status'         => $request->status,
            'priority'       => $request->priority,
            'category'       => $request->category,
            'due_date'       => $request->due_date,
            'due_time'       => $request->due_time,
            'is_recurring'   => $request->boolean('is_recurring'),
            'recur_type'     => $request->recur_type,
            'recur_interval' => $request->recur_interval ?? 1,
            'parent_id'      => null,
            'completed_at'   => null,
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
        ]);

        if ($request->status === 'completed' && !$todo->completed_at) {
            $request->merge(['completed_at' => now()]);

            if ($todo->is_recurring) {
                $this->createNextRecurring($todo);
            }
        }

        $todo->update($request->all());

        return redirect()->route('todo.index')->with('success', 'Tugas berhasil diupdate!');
    }

    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);
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
}
