<div class="filter-bar">
    <button class="btn {{ $filter === 'active' ? 'btn-primary' : 'btn-secondary' }} btn-sm"
        hx-get="/tasks/index.php?filter=active" hx-target="#task-list" hx-push-url="true">Active</button>
    <button class="btn {{ $filter === 'due' ? 'btn-primary' : 'btn-secondary' }} btn-sm"
        hx-get="/tasks/index.php?filter=due" hx-target="#task-list" hx-push-url="true">Due</button>
    <button class="btn {{ $filter === 'upcoming' ? 'btn-primary' : 'btn-secondary' }} btn-sm"
        hx-get="/tasks/index.php?filter=upcoming" hx-target="#task-list" hx-push-url="true">Upcoming</button>
    <button class="btn {{ $filter === 'completed' ? 'btn-primary' : 'btn-secondary' }} btn-sm"
        hx-get="/tasks/index.php?filter=completed" hx-target="#task-list" hx-push-url="true">Completed</button>
</div>

@include('tasks._task-list', ['tasks' => $tasks])
