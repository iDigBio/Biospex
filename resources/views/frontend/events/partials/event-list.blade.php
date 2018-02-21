<a href="{{ route('webauth.events.show', [$event->id]) }}">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">{{ $event->title }}</span>
                <span class="info-box-number">{{ $event->transcriptions_count == 0 ? 0 : $event->transcriptions_count }} Transcriptions</span>
                <span class="progress-description">
                    {{ $event->start_date->diffInHours($event->end_date) }} Hours Remaining
                  </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
</a>