@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

@section('styles')
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
{{ HTML::style('css/ui.jqgrid.css') }}
@stop

{{-- Content --}}
@section('content')
<h3>{{ trans('projects.project') }}: <a href="{{ URL::route('groups.projects.show', [$groupId, $project->id]) }}">{{ $project->title }}</a></h3>
<h4>{{ $expedition->title }}</h4>

<div class="well clearfix">
    <div class="col-md-8">
        <p><strong>@lang('pages.description'):</strong> {{ $expedition->description }} </p>
        <p><strong>@lang('pages.keywords'):</strong> {{ $expedition->keywords }} </p>
    </div>
    <div class="col-md-4">
        <p><em>@lang('pages.created'): {{ $expedition->created_at }}</em></p>
        <p><em>@lang('pages.updated'): {{ $expedition->updated_at }}</em></p>
        <button class="btn btn-success btn-xs" {{ $expedition->exported ? 'disabled="disabled"' : '' }} onClick="location.href='{{ action('process', [$groupId, $project->id, $expedition->id]) }}'">@lang('buttons.process')</button>
        <button class="btn btn-primary btn-xs" type="button" onClick="location.href='{{ URL::route('expedition-dup', [$groupId, $project->id, $expedition->id]) }}'">@lang('buttons.duplicate')</button>
        <button class="btn btn-warning btn-xs" onClick="location.href='{{ action('ExpeditionsController@edit', [$groupId, $project->id, $expedition->id]) }}'">@lang('buttons.edit')</button>
        <button class="btn btn-default btn-xs btn-danger action_confirm" href="{{ URL::route('groups.projects.expeditions.destroy', [$groupId, $project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
    </div>
</div>

<h4>{{ trans('pages.subjects') }}: {{ $expedition->total_subjects }}</h4>
<div class="table-responsive">
    <table id="list"><tr><td></td></tr></table>
    <div id="pager"></div>
</div>
@stop

@section('scripts')
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
{{ HTML::script('js/grid.locale-en.js') }}
{{ HTML::script('js/jquery.jqGrid.min.js') }}
@stop