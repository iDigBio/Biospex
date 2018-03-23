<div class="row top5">
    <div class="col-md-4">{{ $group->title }}</div>
    <div class="col-md-6">
        <div class="bar-container">
            <div class="bar" style="width: {{ GeneralHelper::transcriptionsPercentCompleted($event->transcriptionCount, $group->transcriptionCount) }}%"></div>
        </div>
    </div>
    <div class="col-md-2">
        <div>{{ $group->transcriptionCount }}</div>
    </div>
</div>