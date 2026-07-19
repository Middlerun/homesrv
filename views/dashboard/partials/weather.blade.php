<h2>Weather</h2>
@if(empty($weather))
    <p style="color: #64748b;">Weather not configured.</p>
@else
    <div class="dash-weather-current">
        <div class="dash-weather-temp">{{ $weather['temperature'] }}&deg;</div>
        <div class="dash-weather-desc">{{ $weather['description'] }}</div>
    </div>
    @if(!empty($weather['forecast']))
        <div class="dash-weather-forecast">
            @foreach($weather['forecast'] as $day)
                <div class="dash-weather-day">
                    <div class="dash-weather-day-name">{{ $day['name'] }}</div>
                    <div class="dash-weather-day-temps">{{ $day['high'] }}&deg; / {{ $day['low'] }}&deg;</div>
                </div>
            @endforeach
        </div>
    @endif
@endif
