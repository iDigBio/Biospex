<div class="col-md-12 text-center">
    <div class="btn-group-lg btn-group-vertical mb-2 align-items-center">
        @if($expedition->project->ocrQueue->isEmpty())
            <h4>{{ t('OCR') }}</h4>
            {!! $expedition->present()->expedition_ocr_btn !!}
        @endif
        @php($nfnComplete = false)
        @foreach ($expedition->actors as $actor)
            @if($actor->id == config('config.nfnActorId'))
                @php($nfnComplete = $actor->pivot->state === 3)
                @include('admin.expedition.partials.nfn-btns')
            @endif
            @if($actor->id == config('config.geolocate.actor_id') && $nfnComplete)
                @include('admin.expedition.partials.geolocate-btns')
            @endif
        @endforeach
    </div>
</div>
