@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.biospex_title') }}
@stop

@section('custom-style')
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endsection

{{-- Content --}}
@section('header')
    <header class="header home">
        <nav class="navbar navbar-expand-md">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
        <div class="container text-center">
            <div class="row py-5">
                <div class="col-12" data-aos="fade-right" data-aos-easing="ease-in" data-aos-duration="2000">
                    <h1 class="text-white align-middle home-banner-tag">{{ __('pages.biospex_tag') }}
                        <br>
                        <a href="#learn-more" data-scroll class="btn btn-primary mt-4" data-aos="fade-right"
                           data-aos-easing="ease-out" data-aos-duration="3000">{{ __('pages.learn_more') }}</a>
                    </h1>

                </div>
            </div>
        </div>
    </header>
    @include('front.layout.contributors')
@endsection

@section('content')
    <section class="home-heading text-center" id="learn-more">
        <div class="container">
            <img src="/images/page/logo-tagline-action.png" align="Biospex Tag Line">
            <p class="text-justify mt-4">{{ __('html.biospex_home') }}</p>
        </div>
    </section>
    <!-- Carousel Section -->
    <section>
        <div class="row">
            <div class="col d-flex justify-content-center">
                <div class="col-md-4 text-center" role="group" aria-label="External carousel buttons">
                    <h2>The Process<br>
                        <span class="smallertext">{{ __('pages.carousel_specimen') }}</span></h2>
                    <ul id="external-carousel-btns" class="list-inline">
                        <li data-target="#processCarousel" data-slide-to="0"
                            class="carousel-li-0 active list-inline-item">1
                        </li>
                        <li data-target="#processCarousel" data-slide-to="1" class="carousel-li-1 list-inline-item">2
                        </li>
                        <li data-target="#processCarousel" data-slide-to="2" class="carousel-li-2 list-inline-item">3
                        </li>
                    </ul>
                </div>
                <div id="processCarousel" class="carousel slide col-md-6">
                    <div class="carousel-inner box-shadow inside-carousel">
                        <div class="carousel-item active"
                             style="background-image: url(/images/slider/slider1.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center text-uppercase">{{ __('pages.project') }}</h3>
                                <p>{{ __('pages.carousel_step_1_msg') }}</p>
                            </div>
                        </div>
                        <div class="carousel-item" style="background-image: url(/images/slider/slider2.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center text-uppercase">{{ __('pages.expeditions') }}</h3>
                                <p>{{ __('pages.carousel_step_2_msg') }}</p>
                            </div>
                        </div>
                        <div class="carousel-item" style="background-image: url(/images/slider/slider3.png);">
                            <div class="circle-slider p-5">
                                <h3 class="text-center text-uppercase">{{ __('pages.export') }}</h3>
                                <p>{{ __('pages.carousel_step_3_msg') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tutorial Section -->
    <section class="tutorial">
        <div class="container" style="position:relative;">
            <img src="/images/page/arrow-curved.svg" alt="-->" class="home-arrow d-none d-sm-none d-md-block">
            <div class="row p-5">

                <div class="col-sm-6 mt-5 p-1 pb-md-3">
                    <h2 class="home-header-cta">{{ __('pages.a_project') }}</h2>

                    <div class="card mb-5 px-4 box-shadow" data-aos="fade-down" data-aos-easing="ease-in"
                         data-aos-duration="2000" data-aos-once="true">
                        <h2 class="text-center pt-4">{{ $expedition->project->title }}</h2>
                        <hr>
                        <div class="row card-body pb-2">
                            <div class="col-12">
                                <div class="col-4 float-right">
                                    <img class="img-fluid" src="{{ $expedition->project->present()->logo_url }}"
                                         alt="Card image cap">
                                </div>
                                <p>{{ $expedition->project->description_short }}</p>
                            </div>

                            <div class="col-12">
                                <ul class="text">
                                    <li class="mt-3">{{ $expedition->project->expeditions_count }} {{ __('pages.expeditions') }}</li>
                                    <li>{{ CountHelper::projectTranscriberCount($expedition->project->id) }} {{ __('pages.transcribers') }}</li>
                                    <li>{{ CountHelper::projectTranscriptionCount($expedition->project->id) }} {{ __('pages.transcriptions') }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-footer pb-4">
                            <div class="d-flex align-items-start justify-content-between mt-4">
                                {!! $expedition->project->present()->project_page_icon_lrg !!}
                                {!! $expedition->project->present()->project_events_icon_lrg !!}
                                {!! $expedition->project->present()->organization_icon_lrg !!}
                                {!! $expedition->project->present()->twitter_icon_lrg !!}
                                {!! $expedition->project->present()->facebook_icon_lrg !!}
                                {!! $expedition->project->present()->blog_icon_lrg !!}
                                {!! $expedition->project->present()->contact_email_icon_lrg !!}
                            </div>
                        </div>
                    </div>

                    <h2>{{ __('pages.the_progress') }}</h2>

                    <div class="card mb-4 px-4 box-shadow" data-aos="fade-right" data-aos-easing="ease-in"
                         data-aos-duration="2000" data-aos-once="true">
                        <h2 class="text-center pt-4">{{ __('pages.biospex_stats') }}</h2>
                        <hr>
                        <div class="row card-body pb-5">
                            <div class="col-12">
                                <ul class="text">
                                    <li class="mt-3">{{ $transcriptionCount }} {{ __('pages.launched_transcription_count') }}</li>
                                    <li>{{ $contributorCount }} {{ __('pages.launched_contributor_count') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 p-1 p-md-5 tutorial-right-section">
                    <h2 class="home-header-cta flex-nowrap">An Expedition</h2>
                    <div class="card black mb-4 box-shadow" data-aos="fade-up" data-aos-duration="1500"
                         data-aos-anchor-placement="bottom-bottom" data-aos-once="true">
                        <div class="card-top m-0 p-0">
                            <img class="card-img-top" src="{{ $expedition->logo->url('medium') }}" alt="Card image cap">
                            <div class="card-img-overlay">
                                <h2 class="card-title text-center pt-4">{{ $expedition->title }}</h2>
                                <i class="card-info fas fa-info-circle fa-2x float-right"
                                   style="top: 20rem; left: 31.25rem;"></i>
                                <p>{{ $expedition->description }}</p>
                            </div>
                        </div>

                        <div class="card-body white"
                             style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                            <div class="d-flex justify-content-between">
                                <div class="p-2">
                                    <p>{{ $expedition->stat->transcriptions_completed }} {{ __('pages.transcriptions') }}</p>
                                </div>
                                <div class="p-2"><p>{{ $expedition->stat->percent_completed }}% {{ __('pages.completed') }}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-start justify-content-between mt-4 mx-auto">
                                {!! $expedition->project->present()->project_page_icon_lrg !!}
                                @isset($expedition->panoptesProject)
                                    {!! $expedition->panoptesProject->present()->url_lrg !!}
                                @endisset
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('custom-script')
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            mirror: true
        });
    </script>
    <script src="https://cdn.jsdelivr.net/gh/cferdinandi/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <script>var scroll = new SmoothScroll('a[href*="#"]');</script>

@endsection