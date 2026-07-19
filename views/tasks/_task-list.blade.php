@if(empty($tasks))
    <p style="color: var(--color-text-muted); margin-top: 1rem;">No tasks.</p>
@else
    @foreach($tasks as $task)
        @include('components.task-card', ['task' => $task])
    @endforeach
@endif
