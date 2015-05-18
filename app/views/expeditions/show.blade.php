@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {{ Breadcrumbs::render('projects.expeditions.show', $expedition) }}
    <div class="jumbotron">
        <h4>Expedition:</h4>
        <h2>{{ $expedition->title }}</h2>
        <p>{{ $expedition->description }}</p>
        <p>@lang('pages.keywords'): {{ $expedition->keywords }} </p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                        @if ( ! $expedition->downloads->isEmpty())
                            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ action('DownloadsController@index', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-floppy-save"></span> @lang('buttons.download') </button>
                        @endif
                        <button title="@lang('buttons.dataTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ action('ProjectsController@data', [$expedition->project->id]) }}'"><span class="glyphicon glyphicon-plus-sign"></span> @lang('buttons.data')</button>
                        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@duplicate', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-share-alt"></span> @lang('buttons.duplicate')</button>
                        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" onClick="location.href='{{ action('ExpeditionsController@edit', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-cog"></span> @lang('buttons.edit')</button>
                        <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ action('ExpeditionsController@destroy', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button>
                        </div>
                        <div class="col-md-4">
                            <button title="@lang('buttons.ocrTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@ocr', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-repeat"></span> @lang('buttons.ocr')</button>
                        </div>
                        <div class="col-md-4">
                        @if (is_null($expedition->workflowManager) || $expedition->workflowManager->stopped == 1)
                            <button title="@lang('buttons.processTitle')" class="btn btn-success btn-xs" onClick="location.href='{{ action('ExpeditionsController@process', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-play"></span> @lang('buttons.process')</button>
                        @else
                            <button title="@lang('buttons.stopTitle')" class="btn btn-default btn-xs btn-danger action_confirm" href="{{ action('ExpeditionsController@stop', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-stop"></span> @lang('buttons.stop')</button></td>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            <h4>{{ trans('pages.subjects_assigned') }}: <span id="subjectCount">{{ $expedition->subjectsCount }}</span></h4>
            <div class="table-responsive" id="jqtable">
                <input type="hidden" id="projectId" value="{{ $expedition->project->id }}">
                <input type="hidden" id="expeditionId" value="{{ $expedition->id }}">
                <table id="list"></table>
                <div id="pager"></div>
            </div>
        </div>
    </div>
@stop