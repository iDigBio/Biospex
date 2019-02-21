@extends('front.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Event Team Sign Up') }}
@stop

@section('header')
    <header id="header-img" style="background-image: url(/storage/images/page-banners/banner-image-group.jpg);">
        <nav class="header navbar navbar-expand-md box-shadow">
            <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                             class="my-0 mr-md-auto top-logo font-weight-normal"/></a>
            @include('common.nav')
        </nav>
    </header>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX EVENT TEAM SIGN UP') }}</h2>
    <hr class="header mx-auto" style="width:300px;">

    <div class="col-12 col-md-10 offset-md-1">
        <div class="jumbotron box-shadow py-5 my-5 p-sm-5">
            <div class="col-8 mx-auto">
                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <h4>@lang('pages.event'): {{ $team->event->title }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 col-md-offset-2">
                        <h4>@lang('pages.team'): {{ $team->title }}</h4>
                    </div>
                </div>
                <form action="{{ route('front.events.join', [$team->uuid]) }}" method="post" role="form">
                    {!! csrf_field() !!}
                    <div class="form-group {{ ($errors->has('nfn_user')) ? 'has-error' : '' }}">
                        <label for="name" class="col-form-label required">{{ __('Notes From Nature User Name') }}:</label>
                        @if($active)
                            <input type="text" class="form-control" id="nfn_user" name="nfn_user" value="{{ old('nfn_user') }}" required>
                        @else
                            <input type="text" class="form-control" id="nfn_user" name="nfn_user" value="" placeholder="{{ __('Event Closed') }}" disabled="disabled">
                        @endif
                    </div>
                    @include('common.recaptcha')
                    <div class="form-group text-center">
                        <input type="hidden" name="team_id" value="{{ $team->id }}">
                        <button type="submit" class="btn btn-primary">{{ __('SUBMIT') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop