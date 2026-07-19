@extends('layouts.app')

@section('title', 'Tasks - HomeSrv')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1>Tasks</h1>
        <a href="/tasks/create.php" class="btn btn-primary">Add Task</a>
    </div>

    <div id="task-list">
        @include('tasks._task-filtered', ['tasks' => $tasks, 'filter' => $filter])
    </div>

    <div id="defer-modal"></div>
@endsection
