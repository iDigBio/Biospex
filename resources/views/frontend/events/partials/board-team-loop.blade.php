<div class="row top5">
    <div class="col-md-4">{{ $team->title }}</div>
    <div class="col-md-6">
        <div class="bar-container">
            <div class="bar" style="width: {{ GeneralHelper::transcriptionsPercentCompleted($event->transcriptions_count, $team->transcriptions_count) }}%"></div>
        </div>
    </div>
    <div class="col-md-2">
        <div>{{ $team->transcriptions_count }}</div>
    </div>
</div>