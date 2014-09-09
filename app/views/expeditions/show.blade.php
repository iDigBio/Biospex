@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
<h3>{{ $expedition->title }}</h3>
<h5><a href="{{ URL::route('projects.show', [$project->id]) }}">{{ $project->title }}</a></h5>
<div class="clearfix">
@if (is_null($workflowManager))
	<button class="btn btn-success pull-right" onClick="location.href='{{ action('projects.expeditions.process', [$project->id, $expedition->id]) }}'">@lang('buttons.process')</button>
@else
	<button class="btn btn-default btn-danger pull-right action_confirm" href="{{ URL::route('projects.expeditions.stop', [$project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.stop')</button></td>
@endif
</div>
<div class="well clearfix">
    <div class="col-md-8">
        <p><strong>@lang('pages.description'):</strong> {{ $expedition->description }} </p>
        <p><strong>@lang('pages.keywords'):</strong> {{ $expedition->keywords }} </p>
        @if ( ! empty($expedition->download) && file_exists($filepath))
        <p><strong>@lang('pages.expedition_download'):</strong> <a href="{{ URL::route('projects.expeditions.download', [$project->id, $expedition->id, $expedition->download->id]) }}">{{ $expedition->download->file }}</a> </p>
        @endif
    </div>
    <div class="col-md-2">
        <p><em>@lang('pages.created'): {{ $expedition->created_at }}</em></p>
        <p><em>@lang('pages.updated'): {{ $expedition->updated_at }}</em></p>
    </div>
    <div class="col-md-12">
        <button class="btn btn-default" type="button" onClick="location.href='{{ URL::route('projects.data', [$project->id]) }}'">@lang('buttons.data')</button>
        <button class="btn btn-primary" type="button" onClick="location.href='{{ URL::route('projects.expeditions.duplicate', [$project->id, $expedition->id]) }}'">@lang('buttons.duplicate')</button>
        <button class="btn btn-warning" onClick="location.href='{{ URL::route('projects.expeditions.edit', [$project->id, $expedition->id]) }}'">@lang('buttons.edit')</button>
        <button class="btn btn-default btn-danger action_confirm" href="{{ URL::route('projects.expeditions.destroy', [$project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
    </div>
</div>

<h4>{{ trans('pages.subjects') }}: {{ $expedition->total_subjects }}</h4>
<div class="table-responsive">
    <table id="list"><tr><td></td></tr></table>
    <div id="pager"></div>
</div>
@stop