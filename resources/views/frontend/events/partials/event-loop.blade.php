<a href="{{ route('webauth.events.show', [$event->id]) }}">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="panel panel-default event-card">
            <div class="panel-thumbnail">
                <img src="{{ $event->project->present()->logo_thumb_url }}" alt="{{ $event->project->title }}"
                     class="image img-responsive">
            </div>
            <div class="panel-body">
                <p class="event-text">{{ $event->title }}</p>
                <p class="event-text">{{ $event->transcriptions_count == 0 ? 0 : $event->transcriptions_count }} {{ trans('pages.transcriptions') }}</p>
                <p class="progress-description">
                    {{ GeneralHelper::eventHoursLeft($event->start_date, $event->end_date, $event->timezone) }}
                  </p>
            </div>
        </div>
    </div>
</a>