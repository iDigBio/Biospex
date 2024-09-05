<div class="col-md-12 text-center">
    <div class="btn-group-lg btn-group-vertical mb-2 align-items-center">
        @if($expedition->project->ocrQueue->isEmpty())
            <h4>{{ t('OCR') }}</h4>
            {!! $expedition->present()->expedition_ocr_btn !!}
        @endif
        @php($complete = false)
        @foreach ($expedition->actors as $actor)
            @if($actor->id == config('zooniverse.actor_id'))
                @php($complete = $actor->pivot->state === 3)
                @include('admin.expedition.partials.zooniverse-btns')
            @endif
            @if($actor->id == config('geolocate.actor_id') && $complete)
                @include('admin.expedition.partials.geolocate-btns')
            @endif
        @endforeach
    </div>
</div>
