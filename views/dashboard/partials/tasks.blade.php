<h2>Tasks</h2>
@if(empty($tasks))
    <p style="color: #64748b;">No tasks due.</p>
@else
    @foreach($tasks as $task)
        <div class="dash-task {{ $task['next_due'] < date('Y-m-d') ? 'dash-task-overdue' : ($task['next_due'] === date('Y-m-d') ? 'dash-task-today' : '') }}" id="task-{{ $task['id'] }}">
            <div class="dash-task-info">
                <div class="dash-task-title">{{ $task['title'] }}</div>
                <div class="dash-task-meta">
                    {{ $task['next_due'] }}
                    @if($task['due_time'])
                        {{ substr($task['due_time'], 0, 5) }}
                    @endif
                    @if($task['assigned_name'])
                        &middot; {{ $task['assigned_name'] }}
                    @endif
                    @if($task['recur_type'])
                        &middot; Recurring
                    @endif
                </div>
            </div>
            <div class="dash-task-actions">
                <button class="dash-btn dash-btn-done"
                    hx-post="/tasks/complete.php"
                    hx-vals='{"task_id": {{ $task['id'] }}}'
                    hx-target="#task-{{ $task['id'] }}"
                    hx-swap="outerHTML">Done</button>
                <button class="dash-btn dash-btn-defer"
                    hx-get="/tasks/defer.php?task_id={{ $task['id'] }}"
                    hx-target="#defer-modal"
                    hx-swap="innerHTML">Defer</button>
            </div>
        </div>
    @endforeach
@endif
