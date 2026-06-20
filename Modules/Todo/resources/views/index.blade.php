@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/todo.css') }}">
@endpush

@section('topbar')
<button onclick="openModal()" class="btn btn-primary">+ Tugas Baru</button>
<a href="{{ route('todo.index') }}" class="btn btn-secondary">Timeline</a>
<a href="{{ route('todo.history') }}" class="btn btn-secondary">Riwayat</a>
<span class="task-counter">
    {{ $todayTasks->count() + $overdueTasks->count() }} tugas aktif hari ini
</span>
@endsection

@section('content')

<style>
    .hidden {
        display: none !important;
    }
</style>

<div class="page-header">
    <h2 class="title">Timeline</h2>
</div>

<div class="todo-layout">
    <div class="todo-left">

        <h3 class="section-header">Hari Ini ({{ $todayTasks->count() }})</h3>
        @forelse($todayTasks as $todo)
        <div class="task-card priority-{{ $todo->priority }}">
            <div class="task-card-content">
                @if(!$todo->is_recurring)
                <form action="{{ route('todo.update', $todo->id) }}" method="POST" class="task-toggle-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $todo->title }}">
                    <input type="hidden" name="description" value="{{ $todo->description }}">
                    <input type="hidden" name="priority" value="{{ $todo->priority }}">
                    <input type="hidden" name="category" value="{{ $todo->category }}">
                    <input type="hidden" name="due_date" value="{{ $todo->due_date }}">
                    <input type="hidden" name="due_time" value="{{ $todo->due_time }}">
                    <input type="hidden" name="is_recurring" value="{{ $todo->is_recurring ? 1 : 0 }}">
                    <input type="hidden" name="recur_type" value="{{ $todo->recur_type }}">
                    <input type="hidden" name="recur_interval" value="{{ $todo->recur_interval }}">
                    <input type="hidden" name="status" value="completed">
                    <input type="checkbox" class="task-checkbox" onchange="this.form.submit()">
                </form>
                @endif
                <div class="task-text">
                    <p class="task-title">{{ $todo->title }}</p>
                    <p class="task-meta">
                        <span class="badge badge-priority-{{ $todo->priority }}">
                            {{ $todo->priority === 'high' ? 'Tinggi' : ($todo->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                        </span>
                        {{ $todo->category ?? 'Tidak ada Kategori' }} · {{ \Carbon\Carbon::parse($todo->due_date)->locale('id')->translatedFormat('l') }} ·
                        {{ $todo->due_date }} {{ $todo->due_time }}
                    </p>
                </div>
            </div>
            <div class="task-actions">
                <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary btn-sm">Edit</button>
                <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
            </div>
        </div>
        @empty
        <p class="empty-state">Belum ada tugas untuk hari ini.</p>
        @endforelse


        {{-- OVERDUE --}}
        @if($overdueTasks->count() > 0)
        <div style="margin-top: 2rem;">
            <h3 class="section-header overdue">Terlambat ({{ $overdueTasks->count() }})</h3>
            @foreach($overdueTasks as $todo)
            <div class="task-card priority-{{ $todo->priority }} overdue">
                <div class="task-card-content">
                    @if(!$todo->is_recurring)
                    <form action="{{ route('todo.update', $todo->id) }}" method="POST" class="task-toggle-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="title" value="{{ $todo->title }}">
                        <input type="hidden" name="description" value="{{ $todo->description }}">
                        <input type="hidden" name="priority" value="{{ $todo->priority }}">
                        <input type="hidden" name="category" value="{{ $todo->category }}">
                        <input type="hidden" name="due_date" value="{{ $todo->due_date }}">
                        <input type="hidden" name="due_time" value="{{ $todo->due_time }}">
                        <input type="hidden" name="is_recurring" value="{{ $todo->is_recurring ? 1 : 0 }}">
                        <input type="hidden" name="recur_type" value="{{ $todo->recur_type }}">
                        <input type="hidden" name="recur_interval" value="{{ $todo->recur_interval }}">
                        <input type="hidden" name="status" value="completed">
                        <input type="checkbox" class="task-checkbox" onchange="this.form.submit()">
                    </form>
                    @endif
                    <div class="task-text">
                        <p class="task-title">{{ $todo->title }}</p>
                        <p class="task-meta overdue-meta">
                            <span class="badge badge-priority-{{ $todo->priority }}">
                                {{ $todo->priority === 'high' ? 'Tinggi' : ($todo->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                            </span>
                            {{ $todo->category ?? 'Tidak ada Kategori' }} ·
                            {{ \Carbon\Carbon::parse($todo->due_date)->diffForHumans() }}
                            <span class="badge badge-danger">Terlambat</span>
                        </p>
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary btn-sm">Edit</button>
                    <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- TOMORROW --}}
        @if($tomorrowTasks->count() > 0)
        <div style="margin-top: 2rem;">
            <h3 class="section-header">Besok ({{ $tomorrowTasks->count() }})</h3>
            @foreach($tomorrowTasks as $todo)
            <div class="task-card priority-{{ $todo->priority }}">
                <div class="task-card-content">
                    <div class="task-text">
                        <p class="task-title">{{ $todo->title }}</p>
                        <p class="task-meta">
                            <span class="badge badge-priority-{{ $todo->priority }}">
                                {{ $todo->priority === 'high' ? 'Tinggi' : ($todo->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                            </span>
                            {{ $todo->category ?? 'Tidak ada Kategori' }} · {{ \Carbon\Carbon::parse($todo->due_date)->locale('id')->translatedFormat('l') }} ·
                            {{ $todo->due_date }} {{ $todo->due_time }}
                        </p>
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary btn-sm">Edit</button>
                    <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- THIS WEEK --}}
        @if($weekTasks->count() > 0)
        <div style="margin-top: 2rem;">
            <h3 class="section-header">Minggu Ini ({{ $weekTasks->count() }})</h3>
            @foreach($weekTasks as $todo)
            <div class="task-card priority-{{ $todo->priority }}">
                <div class="task-card-content">
                    <div class="task-text">
                        <p class="task-title">{{ $todo->title }}</p>
                        <p class="task-meta">
                            <span class="badge badge-priority-{{ $todo->priority }}">
                                {{ $todo->priority === 'high' ? 'Tinggi' : ($todo->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                            </span>
                            {{ $todo->category ?? 'Tidak ada Kategori' }} · {{ \Carbon\Carbon::parse($todo->due_date)->locale('id')->translatedFormat('l') }} ·
                            {{ $todo->due_date }} {{ $todo->due_time }}
                        </p>
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary btn-sm">Edit</button>
                    <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- UPCOMING --}}
        @if($upcomingTasks->count() > 0)
        <div style="margin-top: 2rem;">
            <h3 class="section-header">Mendatang ({{ $upcomingTasks->count() }})</h3>
            @foreach($upcomingTasks as $todo)
            <div class="task-card priority-{{ $todo->priority }}">
                <div class="task-card-content">
                    <div class="task-text">
                        <p class="task-title">{{ $todo->title }}</p>
                        <p class="task-meta">
                            <span class="badge badge-priority-{{ $todo->priority }}">
                                {{ $todo->priority === 'high' ? 'Tinggi' : ($todo->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                            </span>
                            {{ $todo->category ?? 'Tidak ada Kategori' }} · {{ $todo->due_date }} {{ $todo->due_time }}
                        </p>
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary btn-sm">Edit</button>
                    <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- NO DUE DATE --}}
        @if($noDueDateTasks->count() > 0)
        <div style="margin-top: 2rem;">
            <h3 class="section-header">Tanpa Deadline ({{ $noDueDateTasks->count() }})</h3>
            @foreach($noDueDateTasks as $todo)
            <div class="task-card priority-{{ $todo->priority }}">
                <div class="task-card-content">
                    <div class="task-text">
                        <p class="task-title">{{ $todo->title }}</p>
                        <p class="task-meta">
                            <span class="badge badge-priority-{{ $todo->priority }}">
                                {{ $todo->priority === 'high' ? 'Tinggi' : ($todo->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                            </span>
                            {{ $todo->category ?? 'Tidak ada Kategori' }}
                        </p>
                        @if($todo->is_recurring && !empty($todo->recur_days))
                        ·
                        {{ collect($todo->recur_days)
            ->map(fn($day) => [
                'mon' => 'Sen',
                'tue' => 'Sel',
                'wed' => 'Rab',
                'thu' => 'Kam',
                'fri' => 'Jum',
                'sat' => 'Sab',
                'sun' => 'Min',
            ][$day] ?? $day)
            ->implode(', ') }}
                        @endif
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary btn-sm">Edit</button>
                    <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- COMPLETED TASKS --}}
        @if($completedTasks->count() > 0)
        <div class="section-container" style="margin-top: 2rem;">
            <h3 class="section-header">Selesai ({{ $completedTasks->count() }})</h3>

            @foreach($completedTasks as $todo)
            <div class="task-card completed">
                <div class="task-card-content">
                    <form action="{{ route('todo.update', $todo->id) }}" method="POST" class="task-toggle-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="title" value="{{ $todo->title }}">
                        <input type="hidden" name="description" value="{{ $todo->description }}">
                        <input type="hidden" name="priority" value="{{ $todo->priority }}">
                        <input type="hidden" name="category" value="{{ $todo->category }}">
                        <input type="hidden" name="due_date" value="{{ $todo->due_date }}">
                        <input type="hidden" name="due_time" value="{{ $todo->due_time }}">
                        <input type="hidden" name="is_recurring" value="{{ $todo->is_recurring ? 1 : 0 }}">
                        <input type="hidden" name="recur_type" value="{{ $todo->recur_type }}">
                        <input type="hidden" name="recur_interval" value="{{ $todo->recur_interval }}">
                        <input type="hidden" name="status" value="pending">
                        <input type="checkbox" class="task-checkbox" onchange="this.form.submit()">
                    </form>
                    <div class="task-text">
                        <p class="task-title">{{ $todo->title }}</p>
                        <p class="task-meta">{{ $todo->category ?? 'Tidak ada Kategori' }} · {{ \Carbon\Carbon::parse($todo->due_date)->locale('id')->translatedFormat('l') }} ·
                            {{ $todo->due_date }} {{ $todo->due_time }}
                        </p>
                    </div>
                </div>
                <div class="task-actions">
                    <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div> {{-- end todo-left --}}

    <div class="todo-right">
        {{-- STATISTIK --}}
        <div class="widget-card">
            <h4 class="widget-title">Statistik</h4>
            <div class="stat-grid">
                <div class="stat-item">
                    <span class="stat-number">{{ $todayTasks->count() }}</span>
                    <span class="stat-label">Hari Ini</span>
                </div>
                <div class="stat-item stat-danger">
                    <span class="stat-number">{{ $overdueTasks->count() }}</span>
                    <span class="stat-label">Terlambat</span>
                </div>
                <div class="stat-item stat-success">
                    <span class="stat-number">{{ $completedTasks->count() }}</span>
                    <span class="stat-label">Selesai</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $todayTasks->count() + $tomorrowTasks->count() + $weekTasks->count() + $upcomingTasks->count() + $noDueDateTasks->count() + $overdueTasks->count() }}</span>
                    <span class="stat-label">Total Aktif</span>
                </div>
            </div>
        </div>

        {{-- MINI KALENDER --}}
        <div class="widget-card" style="margin-top: 1rem;">
            <h4 class="widget-title">
                <button onclick="prevMonth()" class="cal-nav">‹</button>
                <span id="cal-title"></span>
                <button onclick="nextMonth()" class="cal-nav">›</button>
            </h4>
            <div class="mini-calendar">
                <div class="cal-header">
                    <span>Sen</span><span>Sel</span><span>Rab</span>
                    <span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                </div>
                <div id="cal-body" class="cal-body"></div>
            </div>
        </div>
    </div>

</div> {{-- end todo-layout --}}

<div id="task-dates-data" data-dates="{{ $allTasks->pluck('due_date')->filter()->unique()->values()->toJson() }}" style="display:none;"></div>

{{-- MODAL OVERLAY --}}
<div id="modal-overlay" class="modal-overlay" onclick="closeAllModals()"></div>

{{-- MODAL CREATE --}}
<div id="modal-create" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>New Task</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form action="{{ route('todo.store') }}" method="POST">
        @csrf

        <!-- @if($errors->any())
        <div class="form-error">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif -->

        <div class="form-group">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Nama tugas...">
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control" placeholder="Keterangan tambahan...">{{ old('description') }}</textarea>
        </div>

        <div class="form-grid-2">
            <div>
                <label class="form-label">Priority</label>
                <select name="priority" class="form-control">
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </div>

        <div id="due-section-create" class="due-wrapper">
            <div class="form-grid-2" style="margin-top: 1rem;">
                <div class="form-grid-2" style="margin-top: 1rem;">
                    <div>
                        <label class="form-label">Due Date <span class="required">*</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Due Time</label>
                        <input type="time" name="due_time" value="{{ old('due_time') }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
            <label class="form-label">Category</label>
            <input type="text" name="category" value="{{ old('category') }}" class="form-control" placeholder="cth: Aquarium, Keuangan, Pribadi...">
        </div>

        <div class="form-group">
            <label class="form-check">
                <input type="checkbox" name="is_recurring" value="1"
                    {{ old('is_recurring') ? 'checked' : '' }}
                    onchange="toggleRecurring(this, 'recurring-create')">
                Recurring Task
            </label>
        </div>

        <div id="recurring-create" class="form-recurring {{ old('is_recurring') ? 'show' : '' }}">
            <div class="form-grid-2">
                <div>
                    <label class="form-label">Repeat Every</label>
                    <input type="number" name="recur_interval" value="{{ old('recur_interval', 1) }}" min="1" class="form-control">
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select name="recur_type"
                        class="form-control"
                        onchange="handleRecurTypeChange(this)">
                        <option value="daily">Day(s)</option>
                        <option value="weekly">Week(s)</option>
                        <option value="monthly">Month(s)</option>
                    </select>
                </div>
            </div>

            <div id="repeat-days-section" class="form-group">
                <label class="form-label">Ulangi Pada</label>
                <div class="recur-days-grid">
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="mon">
                        <span>Sen</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="tue">
                        <span>Sel</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="wed">
                        <span>Rab</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="thu">
                        <span>Kam</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="fri">
                        <span>Jum</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="sat">
                        <span>Sab</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="sun">
                        <span>Min</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Task</button>
        </div>
    </form>
</div>

{{-- MODAL EDIT --}}
<div id="modal-edit" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Edit Task</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form id="form-edit" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Title <span class="required">*</span></label>
            <input type="text" id="edit-title" name="title" class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea id="edit-description" name="description" rows="3" class="form-control"></textarea>
        </div>

        <div class="form-grid-2">
            <div>
                <label class="form-label">Priority</label>
                <select id="edit-priority" name="priority" class="form-control">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select id="edit-status" name="status" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>

        <div id="due-section-edit" class="due-wrapper">
            <div class="form-grid-2" style="margin-top: 1rem;">
                <div>
                    <label class="form-label">Due Date</label>
                    <input type="date" id="edit-due-date" name="due_date" class="form-control">
                </div>
                <div>
                    <label class="form-label">Due Time</label>
                    <input type="time" id="edit-due-time" name="due_time" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
            <label class="form-label">Category</label>
            <input type="text" id="edit-category" name="category" class="form-control">
        </div>

        <div class="form-group">
            <label class="form-check">
                <input type="checkbox" id="edit-is-recurring" name="is_recurring" value="1"
                    onchange="toggleRecurring(this, 'recurring-edit')">
                Recurring Task
            </label>
        </div>

        <div id="recurring-edit" class="form-recurring">
            <div class="form-grid-2">
                <div>
                    <label class="form-label">Repeat Every</label>
                    <input type="number" id="edit-recur-interval" name="recur_interval" value="1" min="1" class="form-control">
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select id="edit-recur-type"
                        name="recur_type"
                        class="form-control"
                        onchange="handleRecurTypeChange(this)">
                        <option value="daily">Day(s)</option>
                        <option value="weekly">Week(s)</option>
                        <option value="monthly">Month(s)</option>
                    </select>
                </div>
            </div>

            <div id="repeat-days-section-edit" class="form-group">
                <label class="form-label">Ulangi Pada</label>
                <div class="recur-days-grid">
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="mon">
                        <span>Sen</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="tue">
                        <span>Sel</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="wed">
                        <span>Rab</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="thu">
                        <span>Kam</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="fri">
                        <span>Jum</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="sat">
                        <span>Sab</span>
                    </label>
                    <label class="recur-day-label">
                        <input type="checkbox" name="recur_days[]" value="sun">
                        <span>Min</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Task</button>
        </div>
    </form>
</div>

{{-- MODAL DELETE --}}
<div id="modal-delete" class="modal modal-sm modal-delete" aria-hidden="true">
    <div class="modal-header">
        <h3>Hapus Tugas?</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <p id="delete-title-text" class="task-meta"></p>
    <form id="form-delete" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')

@php
$taskDatesJson = $allTasks->pluck('due_date')->filter()->unique()->values()->toJson();
@endphp

<script>
    const taskDates = JSON.parse(document.getElementById('task-dates-data').dataset.dates);
</script>

<script>
    let calDate = new Date();

    function renderCalendar() {
        const year = calDate.getFullYear();
        const month = calDate.getMonth();

        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        document.getElementById('cal-title').textContent = monthNames[month] + ' ' + year;

        const firstDay = new Date(year, month, 1).getDay();
        const startDay = firstDay === 0 ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        const todayStr = today.getFullYear() + '-' +
            String(today.getMonth() + 1).padStart(2, '0') + '-' +
            String(today.getDate()).padStart(2, '0');

        let html = '';

        for (let i = 0; i < startDay; i++) {
            html += '<div class="cal-day empty"></div>';
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = year + '-' +
                String(month + 1).padStart(2, '0') + '-' +
                String(d).padStart(2, '0');

            const isToday = dateStr === todayStr;
            const hasTask = taskDates.includes(dateStr);

            let cls = 'cal-day';
            if (isToday) cls += ' today';
            else if (hasTask) cls += ' has-task';

            html += `<div class="${cls}">${d}</div>`;
        }

        document.getElementById('cal-body').innerHTML = html;
    }

    function prevMonth() {
        calDate.setMonth(calDate.getMonth() - 1);
        renderCalendar();
    }

    function nextMonth() {
        calDate.setMonth(calDate.getMonth() + 1);
        renderCalendar();
    }

    document.addEventListener('DOMContentLoaded', renderCalendar);
</script>

<script>
    const allTasks = @json($allTasks);

    function openModal() {
        document.getElementById('modal-create').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
    }

    function openEditModal(id) {
        const todo = allTasks.find(t => t.id === id);
        if (!todo) return;

        document.getElementById('edit-title').value = todo.title;
        document.getElementById('edit-description').value = todo.description ?? '';
        document.getElementById('edit-priority').value = todo.priority;
        document.getElementById('edit-status').value = todo.status;
        document.getElementById('edit-due-date').value = todo.due_date ?? '';
        document.getElementById('edit-due-time').value = todo.due_time ?? '';
        document.getElementById('edit-category').value = todo.category ?? '';
        document.getElementById('edit-is-recurring').checked = todo.is_recurring == 1;
        document.getElementById('edit-recur-interval').value = todo.recur_interval ?? 1;
        document.getElementById('edit-recur-type').value = todo.recur_type ?? 'daily';
        document.getElementById('recurring-edit').classList.toggle('show', todo.is_recurring);

        // load recur_days
        const recurDaysEdit = document.querySelectorAll('#modal-edit input[name="recur_days[]"]');
        const savedDays = todo.recur_days ?? [];
        recurDaysEdit.forEach(cb => {
            cb.checked = savedDays.includes(cb.value);
        });

        // trigger repeat-days visibility
        const recurTypeEl = document.getElementById('edit-recur-type');
        handleRecurTypeChange(recurTypeEl);

        document.getElementById('form-edit').action = '/todos/' + id;

        document.getElementById('modal-edit').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
    }

    function openDeleteModal(id, title) {
        document.getElementById('delete-title-text').innerText = 'Tugas "' + title + '" akan dihapus permanen.';
        document.getElementById('form-delete').action = '/todos/' + id;

        document.getElementById('modal-delete').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
    }

    function closeAllModals() {
        document.getElementById('modal-create').classList.remove('show');
        document.getElementById('modal-edit').classList.remove('show');
        document.getElementById('modal-delete').classList.remove('show');
        document.getElementById('modal-overlay').classList.remove('show');
    }
</script>

<script>
    function toggleRecurring(el, targetId) {
        const box = document.getElementById(targetId);
        const isRecurring = el.checked;

        // tentukan due-section yang relevan saja
        const modal = el.closest('.modal');
        const dueSection = modal?.querySelector('.due-wrapper');

        if (isRecurring) {
            box.classList.add('show');
            dueSection?.classList.add('hidden');
            modal.querySelectorAll('input[name="due_date"]').forEach(el => {
                el.disabled = true;
                el.value = '';
            });
        } else {
            box.classList.remove('show');
            dueSection?.classList.remove('hidden');
            modal.querySelectorAll('input[name="due_date"]').forEach(el => {
                el.disabled = false;
            });
        }

    }
</script>

<script>
    function handleRecurTypeChange(el) {
        const type = el.value;
        const modal = el.closest('.modal');

        // cari repeat-days-section dalam modal yang aktif
        const repeatDays = modal?.querySelector('[id^="repeat-days-section"]');

        if (!repeatDays) return;

        if (type === 'weekly') {
            repeatDays.style.display = 'block';
        } else {
            repeatDays.style.display = 'none';
            repeatDays.querySelectorAll('input').forEach(cb => {
                cb.checked = false;
            });
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.querySelector('select[name="recur_type"]');

        if (select) {
            handleRecurTypeChange(select);
        }
    });
</script>


@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openModal();
    });
</script>
@endif
@endpush