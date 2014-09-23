@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')

    <ul class="breadcrumb">
    <li><a href="{{ URL::route('projects.show', [$project->id]) }}">{{ $project->title }}</a></li>
    <li>@lang('pages.created'): {{ $expedition->created_at }}</li>
    <li>@lang('pages.updated'): {{ $expedition->updated_at }}</li>
    </ul>

    <div class="jumbotron">
    <h4>Expedition:</h4>
    <h2>{{ $expedition->title }}</h2>
    <p>{{ $expedition->description }}</p>
    <p>@lang('pages.keywords'): {{ $expedition->keywords }} </p>
    </div>

<div class="panel panel-default">
    <div style="padding: 10px;">
    @if ( ! empty($expedition->download) && file_exists($filepath))
        <p class="eyesright"><strong>@lang('pages.expedition_download'):</strong> <a href="{{ URL::route('projects.expeditions.download', [$project->id, $expedition->id, $expedition->download->id]) }}">{{ $expedition->download->file }}</a> </p>
    @endif
    
    <button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ URL::route('projects.data', [$project->id]) }}'">@lang('buttons.data')</button>
    <button class="btn btn-primary btn-xs" type="button" onClick="location.href='{{ URL::route('projects.expeditions.duplicate', [$project->id, $expedition->id]) }}'">@lang('buttons.duplicate')</button>
    <button class="btn btn-warning btn-xs" onClick="location.href='{{ URL::route('projects.expeditions.edit', [$project->id, $expedition->id]) }}'">@lang('buttons.edit')</button>
    <button class="btn btn-default btn-danger action_confirm btn-xs" href="{{ URL::route('projects.expeditions.destroy', [$project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
    
    </div>
</div>


<div class="clearfix">
@if (is_null($workflowManager))
	<button class="btn btn-success pull-right" onClick="location.href='{{ action('projects.expeditions.process', [$project->id, $expedition->id]) }}'">@lang('buttons.process')</button>
@else
	<button class="btn btn-default btn-danger pull-right action_confirm" href="{{ URL::route('projects.expeditions.stop', [$project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.stop')</button></td>
@endif
</div>



<h4>{{ trans('pages.subjects') }}: {{ $expedition->total_subjects }}</h4>
<div class="table-responsive">
    <table id="list"><tr><td></td></tr></table>
    <div id="pager"></div>
</div>
@stop