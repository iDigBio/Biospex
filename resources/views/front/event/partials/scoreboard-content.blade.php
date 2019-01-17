<div class="text-center" style="background-color:#e83f29;">
    <span class="scoreboard-title">{{ $event->title }}</span>
    <h2 class="text-white text modal-number mt-3">{{ $event->transcriptions_count }}</h2>
    <span class="scoreboard-title">Transcriptions</span>
</div>

<table class="table table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th scope="col">Team</th>
        <th scope="col">Transcriptions</th>
    </tr>
    </thead>
    <tbody id="table-rows">
    @php($i = 1)
    @foreach($event->teams as $team)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $team->title }}</td>
            <td>{{ $team->transcriptions_count }}</td>
        </tr>
        @php($i++)
    @endforeach
    </tbody>
</table>

<!-- countdown clock -->
<h2 class="text-center color-action pt-4">{{ __('Time Remaining') }}</h2>
<div class="clockdiv mx-auto">
    <div>
        <span class="days"></span>
        <div class="smalltext">{{ __('Days') }}</div>
    </div>
    <div>
        <span class="hours"></span>
        <div class="smalltext">{{ __('Hours') }}</div>
    </div>
    <div>
        <span class="minutes"></span>
        <div class="smalltext">{{ __('Minutes') }}</div>
    </div>
    <div>
        <span class="seconds"></span>
        <div class="smalltext">{{ __('Seconds') }}</div>
    </div>
</div>
<div id="date" style="display: none">{{ $event->present()->scoreboard_date }}</div>