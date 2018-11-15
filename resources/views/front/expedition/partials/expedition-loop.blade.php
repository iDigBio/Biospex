<div class="col-md-4 mb-4">
    <div class="card mb-4 box-shadow h-100" data-aos="fade-up" data-aos-duration="1500" data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
        <img class="card-img-top" src="{{ $expedition->present()->logo_url }}" alt="Card image cap" style="border-radius: 10px;">

        <div class="card-img-overlay">
            <h2 class="card-title">{{ $expedition->title }}</h2>
            <p>{{ $expedition->description }}</p>

        </div>

        <div class="card-body text-center" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <p><a href="{{ route('projects.get.slug', [$expedition->project->slug]) }}" class="color-action"><i class="fas fa-project-diagram color-action"></i>
                        {{ $expedition->project->title }}</a></p>
                <p>{{ $expedition->stat->percent_completed }}% {{ __('Complete') }}</p>
            </div>

            <div class="d-flex align-items-start justify-content-between">
                <p><a href="#"><i class="far fa-share-square"></i> {{ __('Share') }}</a></p>
                <p>{!! $expedition->nfnWorkflow->present()->nfn_url !!}</p>
            </div>
        </div>
    </div>
</div>