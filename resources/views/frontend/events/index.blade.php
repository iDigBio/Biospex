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
        @each('frontend.events.partials.event-loop', $events, 'event')
    </div>
@stop