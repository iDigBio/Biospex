@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.welcome')
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-md-4 col-md-offset-4 tex">
            <h1>{!!  trans('html.welcome') !!}</h1>
        </div>
    </div>
    <div class="row col-sm-10 col-md-offset-1 top30">
        <div class="col-md-6">
            <h2>@lang('html.welcome_project_title')</h2>
            <div class="top30">{!! trans('html.welcome_project') !!}</div>
        </div>
        <div class="col-md-6">
            <h2>@lang('html.welcome_event_title')</h2>
            <div class="top30">{!! trans('html.welcome_event') !!}</div>
        </div>
    </div>
    <div class="row col-sm-10 col-md-offset-1">
        <div class="col-md-6 text-center">
            <button class="btn btn-primary btn-lg" title="@lang('buttons.createTitleG')"
                    onClick="location.href='{{ route('webauth.groups.create') }}'"><span
                        class="fa fa-group fa-3x"></span></button>
        </div>
        <div class="col-md-6 text-center">
            <button class="btn btn-primary btn-lrg" title="@lang('buttons.createTitleEv')"
                    onClick="location.href='{{ route('webauth.events.create') }}'"><span
                        class="fa fa-calendar fa-5x"></span></button>
        </div>
    </div>
@stop