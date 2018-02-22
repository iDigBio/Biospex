<a href="{{ route('webauth.events.show', [$event->id]) }}">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">{{ $event->title }}</span>
                <span class="info-box-number">{{ $event->transcriptions_count == 0 ? 0 : $event->transcriptions_count }} {{ trans('pages.transcriptions') }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ GeneralHelper::eventStartEndAsPercentage($event->start_date, $event->end_date) }}%"></div>
                </div>
                <span class="progress-description">
                    {{ GeneralHelper::eventHoursLeft($event->start_date, $event->end_date) }}
                  </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
</a>