<div class="text-center" style="background-color:#e83f29;padding-top:35px;">
    <h2 class="text-white text modal-number">{{ $event->transcriptions_count }}<br>
        <small>Transcriptions</small></h2>
</div>
<table class="table table-striped">
    <thead>
    <tr>
        <th scope="col">Team</th>
        <th scope="col">Transcriptions</th>
    </tr>
    </thead>
    <tbody id="table-rows">
    @foreach($event->teams as $team)
        <tr>
            <td>{{ $team->title }}</td>
            <td>{{ $team->transcriptions_count }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<!-- countdown clock -->
<h2 class="text-center color-action pt-4">Time Remaining</h2>
<div id="clockdiv">
    <div>
        <span class="days"></span>
        <div class="smalltext">Days</div>
    </div>
    <div>
        <span class="hours"></span>
        <div class="smalltext">Hours</div>
    </div>
    <div>
        <span class="minutes"></span>
        <div class="smalltext">Minutes</div>
    </div>
    <div>
        <span class="seconds"></span>
        <div class="smalltext">Seconds</div>
    </div>
</div>
<div id="date" style="display: none">{{ $event->present()->scoreboard_date }}</div>