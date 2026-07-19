<div class="modal-overlay" onclick="if(event.target===this) this.remove()">
    <div class="modal">
        <h2>Defer: {{ $task['title'] }}</h2>
        <form hx-post="/tasks/defer.php" hx-target="#task-{{ $task['id'] }}" hx-swap="outerHTML">
            <input type="hidden" name="task_id" value="{{ $task['id'] }}">
            <div class="form-group">
                <label>New due date</label>
                <input type="date" name="new_due" value="{{ date('Y-m-d', strtotime($task['next_due'] . ' +1 day')) }}" required>
            </div>
            <div class="defer-shortcuts">
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.form.new_due.value='{{ date('Y-m-d', strtotime('+1 day')) }}'; this.form.requestSubmit()">Tomorrow</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.form.new_due.value='{{ date('Y-m-d', strtotime('+3 days')) }}'; this.form.requestSubmit()">3 days</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="this.form.new_due.value='{{ date('Y-m-d', strtotime('+7 days')) }}'; this.form.requestSubmit()">1 week</button>
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Defer</button>
                <button type="button" class="btn btn-secondary" onclick="document.querySelector('.modal-overlay').remove()">Cancel</button>
            </div>
        </form>
    </div>
</div>
