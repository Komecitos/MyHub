@extends('layouts.app')

@section('topbar')
<a href="{{ route('todo.index') }}">← Back</a>
<a href="{{ route('todo.index') }}">Timeline</a>
<a href="#">History</a>
@endsection

@section('content')
<div class="page-header">
    <h2 class="title">New Task</h2>
</div>

<form action="{{ route('todo.store') }}" method="POST" class="form-layout">
    @csrf

    <div class="form-group">
        <label class="form-label">Title <span class="required">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Nama tugas...">
        @error('title') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control" placeholder="Keterangan tambahan...">{{ old('description') }}</textarea>
    </div>

    <div class="form-grid">
        <div>
            <label class="form-label">Priority</label>
            <select name="priority" class="form-control">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : 'selected' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
            </select>
        </div>

        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="pending" {{ old('status') == 'pending' ? 'selected' : 'selected' }}>Pending</option>
                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
    </div>

    <div class="form-grid">
        <div>
            <label class="form-label">Due Date</label>
            <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
        </div>

        <div>
            <label class="form-label">Due Time</label>
            <input type="time" name="due_time" value="{{ old('due_time') }}" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Category</label>
        <input type="text" name="category" value="{{ old('category') }}" class="form-control" placeholder="cth: Aquarium, Keuangan, Pribadi...">
    </div>

    <div class="form-group">
        <label class="form-check"><input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }} onchange="toggleRecurring(this)"> Recurring Task</label>
    </div>

    <div id="recurring-options" class="form-recurring">
        <div class="form-grid">
            <div>
                <label class="form-label">Repeat Every</label>
                <input type="number" name="recur_interval" value="{{ old('recur_interval', 1) }}" min="1" class="form-control">
            </div>
            <div>
                <label class="form-label">Type</label>
                <select name="recur_type" class="form-control">
                    <option value="daily">Day(s)</option>
                    <option value="weekly">Week(s)</option>
                    <option value="monthly">Month(s)</option>
                </select>
            </div>
        </div>

        <div class="form-group">

            <label class="form-label">Days</label>

            <label><input type="checkbox" name="recur_days[]" value="mon"> Senin</label>
            <label><input type="checkbox" name="recur_days[]" value="tue"> Selasa</label>
            <label><input type="checkbox" name="recur_days[]" value="wed"> Rabu</label>
            <label><input type="checkbox" name="recur_days[]" value="thu"> Kamis</label>
            <label><input type="checkbox" name="recur_days[]" value="fri"> Jumat</label>
            <label><input type="checkbox" name="recur_days[]" value="sat"> Sabtu</label>
            <label><input type="checkbox" name="recur_days[]" value="sun"> Minggu</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Task</button>
</form>

<script>
    function toggleRecurring(checkbox) {
        document.getElementById('recurring-options').classList.toggle('show', checkbox.checked);
    }
</script>
@endsection