@if($weDigBioDate === null)
    <div class="text-center" style="background-color:#e83f29;">
        <span class="scoreboard-title">{{ t('No current WeDigBio Event') }}</span>
    </div>
@else
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
            <th scope="col">{{ t('Transcriptions') }}</th>
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
@endif