@extends('layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    {{ Breadcrumbs::render('projects.subjects', $project) }}
    <div class="jumbotron">
        <h4>Project:</h4>
        <h2>{{ $project->title }}</h2>
    </div>

    <div class="panel panel-default">
        <div style="padding: 10px;">
            <p class="eyesright"><strong>@lang('pages.project_url'):</strong> {{ HTML::linkAction('HomeController@project', $project->title, [$project->slug]) }} </p>
            <button title="@lang('buttons.dataTitle')" class="btn btn-default btn-xs" type="button" onClick="location.href='{{ URL::route('projects.data', [$project->id]) }}'"><span class="glyphicon glyphicon-plus-sign"></span> @lang('buttons.data')</button>
        </div>
    </div>
    <div class="table-responsive" id="jqtable">
        <input type="hidden" id="projectId" value="{{ $project->id }}">
        <input type="hidden" id="expeditionId" value="0">
        <table id="list"></table>
        <div id="pager"></div>
    </div>
@stop