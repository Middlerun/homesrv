@extends('layouts.app')

@section('title', 'Add Task - HomeSrv')

@section('content')
    <h1>Add Task</h1>

    <form method="POST" action="/tasks/create.php">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required autofocus>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>
        </div>

        <div class="form-group">
            <label for="next_due">Due date</label>
            <input type="date" id="next_due" name="next_due" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-group">
            <label for="due_time">Due time (optional)</label>
            <input type="time" id="due_time" name="due_time">
        </div>

        <div class="form-group">
            <label for="assigned_to">Assign to</label>
            <select id="assigned_to" name="assigned_to">
                <option value="">Anyone</option>
                @foreach($users as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </select>
        </div>

        <fieldset style="border: 1px solid var(--color-border); border-radius: var(--radius); padding: 1rem; margin-bottom: 1rem;">
            <legend style="font-weight: 500;">Recurrence (optional)</legend>

            <div class="form-group">
                <label for="recur_type">Repeat</label>
                <select id="recur_type" name="recur_type" onchange="document.getElementById('recur-options').style.display = this.value ? 'block' : 'none'; document.getElementById('recur-days-group').style.display = this.value === 'weekly' ? 'block' : 'none';">
                    <option value="">One-off</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="interval">Every N days after completion</option>
                </select>
            </div>

            <div id="recur-options" style="display: none;">
                <div class="form-group">
                    <label for="recur_interval">Every</label>
                    <input type="number" id="recur_interval" name="recur_interval" value="1" min="1">
                </div>

                <div class="form-group" id="recur-days-group" style="display: none;">
                    <label>Days of the week</label>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        @foreach(['1' => 'Mon', '2' => 'Tue', '3' => 'Wed', '4' => 'Thu', '5' => 'Fri', '6' => 'Sat', '7' => 'Sun'] as $num => $name)
                            <label style="display: flex; align-items: center; gap: 0.25rem; font-weight: normal;">
                                <input type="checkbox" name="recur_days_list[]" value="{{ $num }}"> {{ $name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </fieldset>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Create Task</button>
            <a href="/tasks/" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
