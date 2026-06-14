@extends('layouts.app')

@section('topbar')
<a href="{{ route('todo.index') }}">← Back</a>
<a href="{{ route('todo.index') }}">Timeline</a>
<a href="#">History</a>
@endsection

@section('content')
<h2 style="margin-bottom: 1.5rem;">New Task</h2>

<form action="{{ route('todo.store') }}" method="POST" style="max-width: 600px;">
    @csrf

    <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Title <span style="color: red;">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}"
            style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;"
            placeholder="Nama tugas...">
        @error('title') <p style="color: red; font-size: 0.8rem;">{{ $message }}</p> @enderror
    </div>

    <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Description</label>
        <textarea name="description" rows="3"
            style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;"
            placeholder="Keterangan tambahan...">{{ old('description') }}</textarea>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Priority</label>
            <select name="priority" style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : 'selected' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
            </select>
        </div>

        <div>
            <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Status</label>
            <select name="status" style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;">
                <option value="pending" {{ old('status') == 'pending' ? 'selected' : 'selected' }}>Pending</option>
                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div>
            <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Due Date</label>
            <input type="date" name="due_date" value="{{ old('due_date') }}"
                style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;">
        </div>

        <div>
            <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Due Time</label>
            <input type="time" name="due_time" value="{{ old('due_time') }}"
                style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;">
        </div>
    </div>

    <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Category</label>
        <input type="text" name="category" value="{{ old('category') }}"
            style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;"
            placeholder="cth: Aquarium, Keuangan, Pribadi...">
    </div>

    <div style="margin-bottom: 1rem;">
        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
            <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                onchange="toggleRecurring(this)">
            Recurring Task
        </label>
    </div>

    <div id="recurring-options" style="display: none; background: #f9f9f9; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Repeat Every</label>
                <input type="number" name="recur_interval" value="{{ old('recur_interval', 1) }}" min="1"
                    style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.3rem; font-weight: 600;">Type</label>
                <select name="recur_type" style="width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="daily">Day(s)</option>
                    <option value="weekly">Week(s)</option>
                    <option value="monthly">Month(s)</option>
                </select>
            </div>
        </div>
    </div>

    <button type="submit"
        style="background: #1e1e2e; color: white; padding: 0.7rem 2rem; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem;">
        Save Task
    </button>
</form>

<script>
    function toggleRecurring(checkbox) {
        document.getElementById('recurring-options').style.display = checkbox.checked ? 'block' : 'none';
    }
</script>
@endsection