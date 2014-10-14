@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.help')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h3>{{ trans('welcome.welcome') }}</h3>
    </div>
    <div class="col-md-10 col-md-offset-1">
            {{ trans('welcome.intro') }}
            {{ trans('welcome.ready') }}
            <button class="btn btn-success" title="@lang('buttons.createTitleP')" onClick="location.href='{{ URL::route('projects.create') }}'"><span class="glyphicon glyphicon-plus"></span>  @lang('buttons.create')</button>
    </div>
</div>
@stop