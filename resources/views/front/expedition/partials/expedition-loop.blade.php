<div class="mx-auto mb-4">
    <div class="card box-shadow h-100">
        <img class="card-img-top" src="{{ $expedition->present()->logo_url }}" alt="Card image cap" style="border-radius: 10px;">
        <div class="card-img-overlay">
            <h3 class="card-title text-center">{{ $expedition->title }}</h3>
            <p>{{ $expedition->description }}</p>
        </div>

        <div class="card-body text-center" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
            <div class="d-flex justify-content-between">
                <div class="p-2">{{ $expedition->stat->transcriptions_completed }} {{ __('Transcriptions') }}</div>
                <div class="p-2">{{ $expedition->stat->percent_completed }}% {{ __('Complete') }}</div>
            </div>
            <hr>
            <div class="col-md-4 d-flex justify-content-between mt-4 mx-auto">
                {!! $expedition->project->present()->project_page_icon !!}
                {!! $expedition->nfnWorkflow->present()->nfn_url !!}
            </div>
        </div>
    </div>
</div>