@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('projects.expeditions.show', $expedition) !!}

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
                            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.downloads.index', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-download fa-lrg"></span> @lang('buttons.download') </button>
                        @endif
                        <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-sm" type="button" onClick="location.href='{{ route('projects.import', [$expedition->project->id]) }}'"><span class="fa fa-plus fa-lrg"></span> @lang('buttons.data')</button>
                        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.duplicate', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-copy fa-lrg"></span> @lang('buttons.duplicate')</button>
                        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" onClick="location.href='{{ route('projects.expeditions.edit', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                        <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-sm" href="{{ route('projects.expeditions.destroy', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
                        </div>
                        <div class="col-md-4">
                            <button title="@lang('buttons.ocrTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.ocr', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-repeat fa-lrg"></span> @lang('buttons.ocr')</button>
                        </div>
                        <div class="col-md-4">
                        @if (is_null($expedition->workflowManager) || $expedition->workflowManager->stopped == 1)
                            <button title="@lang('buttons.processTitle')" class="btn btn-success btn-sm" onClick="location.href='{{ route('projects.expeditions.process', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-play fa-lrg"></span> @lang('buttons.process')</button>
                        @else
                            <button title="@lang('buttons.stopTitle')" class="btn btn-default btn-sm btn-danger action_confirm" href="{{ route('projects.expeditions.stop', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-stop fa-lrg"></span> @lang('buttons.stop')</button></td>
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