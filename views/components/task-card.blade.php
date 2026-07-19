<div class="card task-card {{ $task['next_due'] < date('Y-m-d') ? 'task-overdue' : '' }}" id="task-{{ $task['id'] }}">
    <div class="task-header">
        <div class="task-title"><a href="/tasks/edit.php?id={{ $task['id'] }}">{{ $task['title'] }}</a></div>
        <div class="task-actions">
            @if(!$task['is_completed'])
                <button class="btn btn-success btn-sm"
                    hx-post="/tasks/complete.php"
                    hx-vals='{"task_id": {{ $task['id'] }}}'
                    hx-target="#task-{{ $task['id'] }}"
                    hx-swap="outerHTML">Done</button>
                <button class="btn btn-secondary btn-sm"
                    hx-get="/tasks/defer.php?task_id={{ $task['id'] }}"
                    hx-target="#defer-modal"
                    hx-swap="innerHTML">Defer</button>
            @endif
        </div>
    </div>
    @if($task['description'])
        <div class="task-desc">{{ $task['description'] }}</div>
    @endif
    <div class="task-meta">
        <span class="task-due {{ $task['next_due'] <= date('Y-m-d') ? 'due-now' : '' }}">
            {{ $task['next_due'] }}
            @if($task['due_time'])
                {{ substr($task['due_time'], 0, 5) }}
            @endif
        </span>
        @if($task['recur_type'])
            <span class="task-recur">
                @if($task['recur_type'] === 'daily' && $task['recur_interval'] == 1)
                    Daily
                @elseif($task['recur_type'] === 'daily')
                    Every {{ $task['recur_interval'] }} days
                @elseif($task['recur_type'] === 'weekly' && $task['recur_interval'] == 1)
                    Weekly
                @elseif($task['recur_type'] === 'weekly')
                    Every {{ $task['recur_interval'] }} weeks
                @elseif($task['recur_type'] === 'monthly' && $task['recur_interval'] == 1)
                    Monthly
                @elseif($task['recur_type'] === 'monthly')
                    Every {{ $task['recur_interval'] }} months
                @elseif($task['recur_type'] === 'interval')
                    Every {{ $task['recur_interval'] }} days after completion
                @endif
            </span>
        @endif
        @if(!empty($task['assigned_name']))
            <span class="task-assigned">{{ $task['assigned_name'] }}</span>
        @elseif(!empty($task['creator_name']))
            <span class="task-creator">{{ $task['creator_name'] }}</span>
        @endif
    </div>
</div>
