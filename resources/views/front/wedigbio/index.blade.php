@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('WeDigBio') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/images/page-banners/banner-image-group.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/images/page/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ t('WeDigBio Events') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div id="active-events-main" class="col-sm-12 show">
            <div id="active-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('front.wedigbio.partials.event', ['events' => $events])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/amChartEventRate.min.js')}}"></script>
    <script>
        let eventConfetti = new ConfettiGenerator({target: 'event-conffeti'});
        eventConfetti.render();
    </script>
@endpush