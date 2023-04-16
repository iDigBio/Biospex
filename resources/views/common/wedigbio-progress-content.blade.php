<div class="text-center" style="background-color:#e83f29;">
    <span class="scoreboard-title">{{ $weDigBioDate->present()->progress_title }}</span>
    <h2 class="text-white text modal-number mt-3">{{ $weDigBioDate->transcriptions_count }}</h2>
    <span class="scoreboard-title">{{ t('Transcriptions') }}</span>
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
    @php($i = 1)
    @foreach($weDigBioDate->transcriptions as $transcription)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $transcription->project->title }}</td>
            <td>{{ $transcription->total }}</td>
        </tr>
        @php($i++)
    @endforeach
    </tbody>
</table>

@php($now = \Illuminate\Support\Carbon::now('UTC'))
@if($now->gt($weDigBioDate->end_date))
    <h2 class="text-center pt-4">{{ t('Completed') }}</h2>
@elseif($now->between($weDigBioDate->start_date, $weDigBioDate->end_date))
    <h2 id="inProgress" class="text-center pt-4">{{ t('In Progress') }}</h2>
@endif