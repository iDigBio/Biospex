<div class="text-center" style="background-color:#e83f29;">
    <span class="scoreboard-title">{{ $weDigBioDate->present()->progress_title }}</span>
    <h2 class="text-white text modal-number mt-3">{{ $total }}</h2>
    <span class="scoreboard-title">Transcriptions</span>
</div>

<table class="table table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th scope="col">{{ t('Project') }}</th>
        <th scope="col">{{ t('transcriptions') }}</th>
    </tr>
    </thead>
    <tbody id="table-rows">
    @foreach($transcriptions as $transcription)
        <tr>
            <td>{{ $transcription->project->title }}</td>
            <td>{{ $transcription->total }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- countdown clock -->
@if(false)
<h2 class="text-center color-action pt-4">{{ t('Time Remaining') }}</h2>
<div class="clockdiv mx-auto">
    <div>
        <span class="days"></span>
        <div class="smalltext">{{ t('Days') }}</div>
    </div>
    <div>
        <span class="hours"></span>
        <div class="smalltext">{{ t('Hours') }}</div>
    </div>
    <div>
        <span class="minutes"></span>
        <div class="smalltext">{{ t('Minutes') }}</div>
    </div>
    <div>
        <span class="seconds"></span>
        <div class="smalltext">{{ t('Seconds') }}</div>
    </div>
</div>
<div id="date" style="display: none">{{ $event->present()->scoreboard_date }}</div>
@else
    <h2 class="text-center pt-4">{{ t('Completed') }}</h2>
@endif