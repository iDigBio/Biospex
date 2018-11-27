<div class="col-md-4 mb-4">
    <div class="card-project mb-4 px-4 box-shadow h-100">
        <h2 class="text-center pt-4">{{ $project->title }}</h2>
        <hr>
        <div class="row card-body">
            <div class="col-7">
                <ul>
                    <li>{{ $project->description_short }}</li>
                    <li class="mt-3">{{ $project->expeditions_count }} {{ __('Expeditions') }}</li>
                    <li>{{ $project->transcriptions_count }} {{ __('Transcriptions') }}</li>
                </ul>
            </div>

            <div class="col-5">
                <img class="img-fluid" src="{{ $project->present()->logo_thumb_url }}" alt="Card image cap">
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                {!! $project->present()->project_page_icon !!}
                {!! $project->present()->project_events_icon !!}
                {!! $project->present()->organization_icon !!}
                {!! $project->present()->twitter_icon !!}
                {!! $project->present()->facebook_icon !!}
                {!! $project->present()->contact_email_icon !!}
            </div>
        </div>
    </div>
</div>