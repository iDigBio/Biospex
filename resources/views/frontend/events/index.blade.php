@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ __('pages.events') }}
@stop

{{-- Content --}}
@section('content')
    <div class="row top25">
        <div class="col-md-10 col-md-offset-1 text-center">
            <button title="{{ __('pages.createTitleEv') }}" class="btn btn-success btn-lg"
                    onClick="location.href='{{ route('admin.events.create') }}'">
                <i class="fa fa-calendar fa-2x"></i>
                <h2>{{ __('pages.create') }} {{ __('pages.event') }}</h2></button>
        </div>
    </div>
    <div class="row top25">
        @each('front.events.partials.event-loop', $events, 'event')
    </div>
    @include('front.events.scoreboard')
@stop