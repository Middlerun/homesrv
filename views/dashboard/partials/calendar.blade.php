<h2>Calendar</h2>
@if(empty($events))
    <p style="color: #64748b;">No upcoming events.</p>
@else
    @php $currentDate = null; @endphp
    @foreach($events as $event)
        @if($event['date'] !== $currentDate)
            @php $currentDate = $event['date']; @endphp
            <div class="dash-event-date">{{ date('D j M', strtotime($currentDate)) }}</div>
        @endif
        <div class="dash-event">
            <div class="dash-event-time">{{ $event['time'] ?? 'All day' }}</div>
            <div class="dash-event-title" style="border-left: 3px solid {{ $event['color'] ?? '#3b82f6' }}; padding-left: 0.5rem;">
                {{ $event['summary'] }}
            </div>
        </div>
    @endforeach
@endif
