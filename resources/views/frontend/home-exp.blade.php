<div class="col-12 col-sm-12 col-md-6 col-lg-6 p-1 p-md-5 tutorial-right-section">
    <h2 class="home-header-cta flex-nowrap">{{ __('An Expedition') }}</h2>
    <div class="card mb-4 box-shadow" data-aos="fade-up" data-aos-duration="1500"
         data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
        <!-- overlay -->
        <div id="overlay">
            <div class="overlay-text">
                <p>{{ $expedition->description }}</p>
            </div>
        </div>
        <!-- end overlay -->

        <img class="card-img-top" src="/images/card-exp-image.jpg" alt="Card image cap">
        <a href="#" class="View-overlay"><h2 class="card-title">{{ $expedition->title }} <i
                        class="fa fa-angle-right text-white align-middle"> </i></h2></a>

        <div class="card-body text-center">
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