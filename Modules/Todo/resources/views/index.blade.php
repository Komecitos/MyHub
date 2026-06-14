@extends('layouts.app')

@section('topbar')
<button onclick="openModal()" class="btn btn-primary">+ New Task</button>
<a href="{{ route('todo.index') }}" class="btn btn-secondary">Timeline</a>
<a href="#" class="btn btn-secondary">History</a>
@endsection

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/todo.css') }}">
@endpush

@if(session('success'))
<div style="background: rgba(7, 50, 26, 0.4); color: #b7f3c4; padding: var(--space-sm); border-radius: var(--radius-sm); margin-bottom: var(--space-md); border: 1px solid rgba(183,243,196,0.06);">
    {{ session('success') }}
</div>
@endif

<h2 style="margin-bottom: var(--space-md); color: var(--text-primary);">Timeline</h2>

@forelse($todayTasks as $todo)
<div class="todo-card priority-{{ $todo->priority }}">
    <div class="todo-card-content">
        <form action="{{ route('todo.update', $todo->id) }}" method="POST" style="margin: 0;">
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
            <input type="checkbox" class="todo-checkbox" onchange="this.form.submit()" {{ $todo->status == 'completed' ? 'checked' : '' }}>
        </form>
        <div class="todo-card-text">
            <p class="todo-card-title">{{ $todo->title }}</p>
            <p class="todo-card-meta">
                {{ $todo->category ?? 'No category' }} · {{ $todo->due_date }} {{ $todo->due_time }}
            </p>
        </div>
    </div>
    <div class="todo-card-actions">
        <button onclick="openEditModal({{ $todo->id }})" class="btn btn-secondary">Edit</button>
        <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" class="btn btn-danger">Delete</button>
    </div>
</div>
@empty
<p style="color:#9ca3af;">Belum ada tugas untuk hari ini.</p>
@endforelse

{{-- COMPLETED TASKS --}}
@if($completedTasks->count() > 0)
<div style="margin-top:2rem;">
    <h3 style="color:#9ca3af; font-size:0.95rem; margin-bottom:1rem; text-transform:uppercase; letter-spacing:1px;">
        Completed ({{ $completedTasks->count() }})
    </h3>

    @foreach($completedTasks as $todo)
    <div style="background: linear-gradient(180deg, rgba(10,12,16,0.6), rgba(10,12,16,0.5)); border-radius:8px; padding:1rem 1.25rem; margin-bottom:0.75rem; display:flex; justify-content:space-between; align-items:center; opacity:0.7; border-left: 4px solid rgba(255,255,255,0.04);">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <form action="{{ route('todo.update', $todo->id) }}" method="POST">
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
                <input type="checkbox" checked onchange="this.form.submit()" style="width:18px; height:18px; cursor:pointer;">
            </form>
            <div>
                <p style="font-weight:600; text-decoration:line-through; color:#9ca3af;">{{ $todo->title }}</p>
                <p style="font-size:0.8rem; color:#9ca3af;">
                    {{ $todo->category ?? 'No category' }} · Selesai: {{ $todo->completed_at ? \Carbon\Carbon::parse($todo->completed_at)->format('d M Y') : '-' }}
                </p>
            </div>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button onclick="openDeleteModal({{ $todo->id }}, '{{ addslashes($todo->title) }}')" style="padding:0.4rem 0.9rem; border:1px solid rgba(231,76,60,0.18); border-radius:6px; background:transparent; color:#fb7185; cursor:pointer;">Delete</button>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- MODAL OVERLAY --}}
<div id="modal-overlay" onclick="closeAllModals()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999;"></div>

{{-- MODAL CREATE --}}
<div id="modal-create" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#071220; border-radius:12px; padding:2rem; width:90%; max-width:550px; z-index:1000; max-height:90vh; overflow-y:auto; box-shadow:0 18px 60px rgba(2,6,23,0.8); border:1px solid rgba(255,255,255,0.04);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h3 style="color:#e5e7eb;">New Task</h3>
        <button onclick="closeAllModals()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#9ca3af;">&times;</button>
    </div>
    <form action="{{ route('todo.store') }}" method="POST">
        @csrf

        @if($errors->any())
        <div style="background:linear-gradient(180deg, rgba(255,50,50,0.06), rgba(0,0,0,0.0)); color:#fecaca; padding:0.75rem 1rem; border-radius:6px; margin-bottom:1rem; border:1px solid rgba(231,76,60,0.06);">
            <ul style="margin:0; padding-left:1.2rem;">
                @foreach($errors->all() as $error)
                <li style="font-size:0.85rem;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div style="margin-bottom:1rem;">
            <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Title <span style="color:red;">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;" placeholder="Nama tugas...">
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Description</label>
            <textarea name="description" rows="3" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;" placeholder="Keterangan tambahan...">{{ old('description') }}</textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Priority</label>
                <select name="priority" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Status</label>
                <select name="status" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Due Date <span style="color:red;">*</span></label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
            </div>
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Due Time</label>
                <input type="time" name="due_time" value="{{ old('due_time') }}" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
            </div>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Category</label>
            <input type="text" name="category" value="{{ old('category') }}" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;" placeholder="cth: Aquarium, Keuangan, Pribadi...">
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:flex; align-items:center; gap:0.5rem; font-weight:600; color:#e5e7eb;">
                <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }} onchange="toggleRecurring(this, 'recurring-create')">
                Recurring Task
            </label>
        </div>

        <div id="recurring-create" style="display:{{ old('is_recurring') ? 'block' : 'none' }}; background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.02)); padding:1rem; border-radius:8px; margin-bottom:1rem; border:1px solid rgba(255,255,255,0.02);">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Repeat Every</label>
                    <input type="number" name="recur_interval" value="{{ old('recur_interval', 1) }}" min="1" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Type</label>
                    <select name="recur_type" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                        <option value="daily" {{ old('recur_type') == 'daily' ? 'selected' : '' }}>Day(s)</option>
                        <option value="weekly" {{ old('recur_type') == 'weekly' ? 'selected' : '' }}>Week(s)</option>
                        <option value="monthly" {{ old('recur_type') == 'monthly' ? 'selected' : '' }}>Month(s)</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
            <button type="button" onclick="closeAllModals()" style="padding:0.6rem 1.5rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb; cursor:pointer;">Cancel</button>
            <button type="submit" style="padding:0.6rem 1.5rem; background:#4f46e5; color:white; border:none; border-radius:6px; cursor:pointer;">Save Task</button>
        </div>
    </form>
</div>

{{-- MODAL EDIT --}}
<div id="modal-edit" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#071220; border-radius:12px; padding:2rem; width:90%; max-width:550px; z-index:1000; max-height:90vh; overflow-y:auto; box-shadow:0 18px 60px rgba(2,6,23,0.8); border:1px solid rgba(255,255,255,0.04);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h3 style="color:#e5e7eb;">Edit Task</h3>
        <button onclick="closeAllModals()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#9ca3af;">&times;</button>
    </div>
    <form id="form-edit" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:1rem;">
            <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Title <span style="color:#fb7185;">*</span></label>
            <input type="text" id="edit-title" name="title" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Description</label>
            <textarea id="edit-description" name="description" rows="3" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;"></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Priority</label>
                <select id="edit-priority" name="priority" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Status</label>
                <select id="edit-status" name="status" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Due Date <span style="color:#fb7185;">*</span></label>
                <input type="date" id="edit-due-date" name="due_date" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
            </div>
            <div>
                <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Due Time</label>
                <input type="time" id="edit-due-time" name="due_time" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
            </div>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block; margin-bottom:0.3rem; font-weight:600; color:#e5e7eb;">Category</label>
            <input type="text" id="edit-category" name="category" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:flex; align-items:center; gap:0.5rem; font-weight:600; color:#e5e7eb;">
                <input type="checkbox" id="edit-is-recurring" name="is_recurring" value="1" onchange="toggleRecurring(this, 'recurring-edit')">
                Recurring Task
            </label>
        </div>

        <div id="recurring-edit" style="display:none; background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.02)); padding:1rem; border-radius:8px; margin-bottom:1rem; border:1px solid rgba(255,255,255,0.02);">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Repeat Every</label>
                    <input type="number" id="edit-recur-interval" name="recur_interval" value="1" min="1" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.3rem; font-weight:600;">Type</label>
                    <select id="edit-recur-type" name="recur_type" style="width:100%; padding:0.6rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb;">
                        <option value="daily">Day(s)</option>
                        <option value="weekly">Week(s)</option>
                        <option value="monthly">Month(s)</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
            <button type="button" onclick="closeAllModals()" style="padding:0.6rem 1.5rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb; cursor:pointer;">Cancel</button>
            <button type="submit" style="padding:0.6rem 1.5rem; background:#4f46e5; color:white; border:none; border-radius:6px; cursor:pointer;">Update Task</button>
        </div>
    </form>
</div>

{{-- MODAL DELETE --}}
<div id="modal-delete" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#071220; border-radius:12px; padding:2rem; width:90%; max-width:400px; z-index:1000; text-align:center; box-shadow:0 18px 60px rgba(2,6,23,0.8); border:1px solid rgba(255,255,255,0.04);">
    <h3 style="margin-bottom:0.75rem; color:#e5e7eb;">Hapus Tugas?</h3>
    <p id="delete-title-text" style="color:#9ca3af; margin-bottom:1.5rem; font-size:0.9rem;"></p>
    <form id="form-delete" method="POST">
        @csrf
        @method('DELETE')
        <div style="display:flex; justify-content:center; gap:0.75rem;">
            <button type="button" onclick="closeAllModals()" style="padding:0.6rem 1.5rem; border:1px solid rgba(255,255,255,0.04); border-radius:6px; background:transparent; color:#e5e7eb; cursor:pointer;">Cancel</button>
            <button type="submit" style="padding:0.6rem 1.5rem; background:#ef4444; color:white; border:none; border-radius:6px; cursor:pointer;">Hapus</button>
        </div>
    </form>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openModal();
    });
</script>
@endif

<script>
    const allTasks = @json($allTasks);

    function openModal() {
        document.getElementById('modal-create').style.display = 'block';
        document.getElementById('modal-overlay').style.display = 'block';
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
        document.getElementById('recurring-edit').style.display = todo.is_recurring ? 'block' : 'none';

        document.getElementById('form-edit').action = '/todos/' + id;

        document.getElementById('modal-edit').style.display = 'block';
        document.getElementById('modal-overlay').style.display = 'block';
    }

    function openDeleteModal(id, title) {
        document.getElementById('delete-title-text').innerText = 'Tugas "' + title + '" akan dihapus permanen.';
        document.getElementById('form-delete').action = '/todos/' + id;

        document.getElementById('modal-delete').style.display = 'block';
        document.getElementById('modal-overlay').style.display = 'block';
    }

    function closeAllModals() {
        document.getElementById('modal-create').style.display = 'none';
        document.getElementById('modal-edit').style.display = 'none';
        document.getElementById('modal-delete').style.display = 'none';
        document.getElementById('modal-overlay').style.display = 'none';
    }

    function toggleRecurring(checkbox, targetId) {
        document.getElementById(targetId).style.display = checkbox.checked ? 'block' : 'none';
    }
</script>

@endsection