@extends('layouts.app')

@section('title', 'Edit Task - HomeSrv')

@section('content')
    <h1>Edit Task</h1>

    <form method="POST" action="/tasks/edit.php?id={{ $task['id'] }}">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ $task['title'] }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description">{{ $task['description'] }}</textarea>
        </div>

        <div class="form-group">
            <label for="next_due">Due date</label>
            <input type="date" id="next_due" name="next_due" value="{{ $task['next_due'] }}" required>
        </div>

        <div class="form-group">
            <label for="due_time">Due time (optional)</label>
            <input type="time" id="due_time" name="due_time" value="{{ $task['due_time'] }}">
        </div>

        <div class="form-group">
            <label for="assigned_to">Assign to</label>
            <select id="assigned_to" name="assigned_to">
                <option value="">Anyone</option>
                @foreach($users as $user)
                    <option value="{{ $user['id'] }}" {{ $task['assigned_to'] == $user['id'] ? 'selected' : '' }}>{{ $user['name'] }}</option>
                @endforeach
            </select>
        </div>

        <fieldset style="border: 1px solid var(--color-border); border-radius: var(--radius); padding: 1rem; margin-bottom: 1rem;">
            <legend style="font-weight: 500;">Recurrence</legend>

            <div class="form-group">
                <label for="recur_type">Repeat</label>
                <select id="recur_type" name="recur_type" onchange="document.getElementById('recur-options').style.display = this.value ? 'block' : 'none'; document.getElementById('recur-days-group').style.display = this.value === 'weekly' ? 'block' : 'none';">
                    <option value="">One-off</option>
                    <option value="daily" {{ $task['recur_type'] === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $task['recur_type'] === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $task['recur_type'] === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="interval" {{ $task['recur_type'] === 'interval' ? 'selected' : '' }}>Every N days after completion</option>
                </select>
            </div>

            <div id="recur-options" style="display: {{ $task['recur_type'] ? 'block' : 'none' }};">
                <div class="form-group">
                    <label for="recur_interval">Every</label>
                    <input type="number" id="recur_interval" name="recur_interval" value="{{ $task['recur_interval'] ?? 1 }}" min="1">
                </div>

                @php $selectedDays = $task['recur_days'] ? explode(',', $task['recur_days']) : [] @endphp
                <div class="form-group" id="recur-days-group" style="display: {{ $task['recur_type'] === 'weekly' ? 'block' : 'none' }};">
                    <label>Days of the week</label>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        @foreach(['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat', '7' => 'Sun'] as $num => $name)
                            <label style="display: flex; align-items: center; gap: 0.25rem; font-weight: normal;">
                                <input type="checkbox" name="recur_days_list[]" value="{{ $num }}" {{ in_array($num, $selectedDays) ? 'checked' : '' }}> {{ $name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </fieldset>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="/tasks/" class="btn btn-secondary">Cancel</a>
        </div>

        <form method="POST" action="/tasks/delete.php" style="margin-top: 1rem;" onsubmit="return confirm('Delete this task?')">
            <input type="hidden" name="task_id" value="{{ $task['id'] }}">
            <button type="submit" class="btn btn-danger">Delete Task</button>
        </form>
    </form>
@endsection
