<div class="mx-auto mb-4">
    <div class="card black box-shadow h-100">
        <div class="card-top m-0 p-0">
            <img class="card-img-top" src="{{ $expedition->present()->logo_url }}" alt="Card image cap">
            <div class="card-img-overlay">
                <h2 class="card-title text-center pt-3">{{ $expedition->title }}</h2>
                <i class="card-info fas fa-info-circle float-right"></i>
                <p>{{ $expedition->description }}</p>
            </div>
        </div>

        <div class="card-body card-body-img-top white text-center">
            <div class="d-flex justify-content-between">
                <p>
                    <small>{{ $expedition->stat->transcriptions_completed }} {{ __('pages.transcriptions') }}</small>
                </p>
                <p>
                    <small>{{ $expedition->stat->percent_completed }}% {{ __('pages.completed') }}</small>
                </p>
            </div>
            <hr>
            <div class="d-flex align-items-start justify-content-between mt-4 mx-auto">
                {!! $expedition->project->present()->project_page_icon !!}
                @isset($expedition->nfnWorkflow)
                    @if ($expedition->nfnActor->pivot->completed === 0)
                        {!! $expedition->nfnWorkflow->present()->nfn_url !!}
                    @endif
                @endisset
            </div>
        </div>
    </div>
</div>