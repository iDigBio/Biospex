<div class="text-center mt-4" style="background-color:#e83f29;padding-top:5px;">
    <h3 class="text-white text modal-number">{{ $event->title }}</h3>
    <h2 class="text-white text modal-number" style="padding-bottom: 10px;">{{ $event->transcriptions_count }}</h2>
    <span style="font-size: 35px; color: #fff;">Transcriptions</span>
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
</div>
<div id="date" style="display: none">{{ $event->present()->scoreboard_date }}</div>