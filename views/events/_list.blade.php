@if(!empty($status))
    <p style="color: var(--color-{{ $status['type'] === 'success' ? 'success' : 'danger' }}); margin-bottom: 1rem;">{{ $status['message'] }}</p>
@endif
@if(empty($events))
    <p style="color: var(--color-text-muted); margin-top: 1rem;">No upcoming events.</p>
@else
    @php $currentDate = null; @endphp
    @foreach($events as $event)
        @if($event['date'] !== $currentDate)
            @php $currentDate = $event['date']; @endphp
            <h2 class="event-date">{{ date('D j M', strtotime($currentDate)) }}</h2>
        @endif
        <div class="event-row {{ $event['hidden'] ? 'event-hidden' : '' }}">
            <div class="event-time">{{ $event['time'] ?? 'All day' }}</div>
            <div class="event-summary">
                <span class="event-color" style="background: {{ $event['color'] ?? '#3b82f6' }};"></span>
                {{ $event['summary'] }}
            </div>
            <div class="event-feed">{{ $event['feed_name'] }}</div>
            <div class="event-action">
                @if($event['hidden'])
                    <form hx-post="/events/restore.php" hx-target="#events-list" hx-swap="innerHTML" style="margin: 0;">
                        <input type="hidden" name="feed_id" value="{{ $event['feed_id'] }}">
                        <input type="hidden" name="uid" value="{{ $event['uid'] }}">
                        <button type="submit" class="btn btn-secondary btn-sm">Restore to dashboard</button>
                    </form>
                @else
                    <form hx-post="/events/hide.php" hx-target="#events-list" hx-swap="innerHTML" style="margin: 0;">
                        <input type="hidden" name="feed_id" value="{{ $event['feed_id'] }}">
                        <input type="hidden" name="uid" value="{{ $event['uid'] }}">
                        <button type="submit" class="btn btn-secondary btn-sm">Hide from dashboard</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
@endif
