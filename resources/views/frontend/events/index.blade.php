@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.events')
@stop

{{-- Content --}}
@section('content')
    <div class="row top25">
        <div class="col-md-10 col-md-offset-1 text-center">
            <button title="@lang('pages.createTitleEv')" class="btn btn-success btn-lg"
                    onClick="location.href='{{ route('webauth.events.create') }}'">
                <i class="fa fa-calendar fa-2x"></i>
                <h2>@lang('pages.create') @lang('pages.event')</h2></button>
        </div>
    </div>
    <div class="row top25">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="panel panel-default event-card">
                <div class="panel-thumbnail"><img src="/storage/logos/original/missing.png" width="100%" class="img-responsive">
                </div>
                <div class="panel-body">
                    <p class="lead">Hacker News</p>
                    <p>120k Followers, 900 Posts</p>
                    <p>
                        <img src="https://lh4.googleusercontent.com/-eSs1F2O7N1A/AAAAAAAAAAI/AAAAAAAAAAA/caHwQFv2RqI/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                        <img src="https://lh4.googleusercontent.com/-9Yw2jNffJlE/AAAAAAAAAAI/AAAAAAAAAAA/u3WcFXvK-g8/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                    </p>
                </div>
            </div>
        </div><!--/col-->

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="panel panel-default event-card">
                <div class="panel-thumbnail"><img src="/storage/logos/original/missing.png" width="100%" class="img-responsive">
                </div>
                <div class="panel-body">
                    <p class="lead">Hacker News</p>
                    <p>120k Followers, 900 Posts</p>
                    <p>
                        <img src="https://lh4.googleusercontent.com/-eSs1F2O7N1A/AAAAAAAAAAI/AAAAAAAAAAA/caHwQFv2RqI/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                        <img src="https://lh4.googleusercontent.com/-9Yw2jNffJlE/AAAAAAAAAAI/AAAAAAAAAAA/u3WcFXvK-g8/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                    </p>
                </div>
            </div>
        </div><!--/col-->

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="panel panel-default event-card">
                <div class="panel-thumbnail"><img src="/storage/logos/original/missing.png" width="100%" class="img-responsive">
                </div>
                <div class="panel-body">
                    <p class="lead">Hacker News</p>
                    <p>120k Followers, 900 Posts</p>
                    <p>
                        <img src="https://lh4.googleusercontent.com/-eSs1F2O7N1A/AAAAAAAAAAI/AAAAAAAAAAA/caHwQFv2RqI/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                        <img src="https://lh4.googleusercontent.com/-9Yw2jNffJlE/AAAAAAAAAAI/AAAAAAAAAAA/u3WcFXvK-g8/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                    </p>
                </div>
            </div>
        </div><!--/col-->

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="panel panel-default event-card">
                <div class="panel-thumbnail"><img src="/storage/logos/original/missing.png" width="100%" class="img-responsive">
                </div>
                <div class="panel-body">
                    <p class="lead">Hacker News</p>
                    <p>120k Followers, 900 Posts</p>
                    <p>
                        <img src="https://lh4.googleusercontent.com/-eSs1F2O7N1A/AAAAAAAAAAI/AAAAAAAAAAA/caHwQFv2RqI/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                        <img src="https://lh4.googleusercontent.com/-9Yw2jNffJlE/AAAAAAAAAAI/AAAAAAAAAAA/u3WcFXvK-g8/s28-c-k-no/photo.jpg"
                             width="28px" height="28px">
                    </p>
                </div>
            </div>
        </div><!--/col-->

    </div>
@stop