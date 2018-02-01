@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('events.events')
@stop

{{-- Content --}}
@section('content')
    <div class="row top10">
        <div class="col-md-10 col-md-offset-1 text-center">
        <button title="@lang('buttons.createTitleEv')" class="btn btn-success btn-lg"
                onClick="location.href='{{ route('webauth.events.create') }}'">
            <i class="fa fa-calendar fa-5x"></i><h2>Create Event</h2></button>
        </div>
    </div>
    @include('frontend.events.partials.event-list')
@stop