<div class="col-md-4 mb-4">
    <div class="card box-shadow h-100" data-aos="fade-up" data-aos-duration="1500" data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
        <img class="card-img-top" src="{{ $expedition->present()->logo_url }}" alt="Card image cap" style="border-radius: 10px;">

        <div class="card-img-overlay">
            <h2 class="card-title">{{ $expedition->title }}</h2>
            <p>{{ $expedition->description }}</p>

        </div>

        <div class="card-body text-center" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
            <p>{{ $expedition->stat->percent_completed }}% {{ __('Complete') }}</p>
            <hr>
            <div class="col-md-4 d-flex justify-content-between mt-4 mx-auto">
                {!! $expedition->project->present()->project_page_icon !!}
                {!! $expedition->nfnWorkflow->present()->nfn_url !!}
            </div>
        </div>
    </div>
</div>