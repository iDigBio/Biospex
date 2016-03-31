@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('projects.expeditions.get.show', $expedition) !!}
    @include('frontend.partials.process-modal')
    <div class="jumbotron">
        <h3>{{ $expedition->title }}</h3>
        <p>{{ $expedition->description }}
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                        @if ( ! $expedition->downloads->isEmpty())
                            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.downloads.get.index', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-download fa-lrg"></span> @lang('buttons.download') </button>
                        @endif
                        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.get.duplicate', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-copy fa-lrg"></span> @lang('buttons.duplicate')</button>
                        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" onClick="location.href='{{ route('projects.expeditions.get.edit', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                        <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-sm" href="{{ route('projects.expeditions.delete.delete', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
                        </div>
                        <div class="col-md-2">
                            <button title="@lang('buttons.ocrTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.get.ocr', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-repeat fa-lrg"></span> @lang('buttons.ocr')</button>
                        </div>
                        <div class="col-md-2">
                        @if (is_null($expedition->workflowManager) || $expedition->workflowManager->stopped == 1)
                            <button title="@lang('buttons.processTitle')" class="btn btn-success btn-sm" onClick="location.href='{{ route('projects.expeditions.get.process', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-play fa-lrg"></span> @lang('buttons.process')</button>
                        @else
                            <button title="@lang('buttons.stopTitle')" class="btn btn-default btn-sm btn-danger action_confirm" href="{{ route('projects.expeditions.delete.stop', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-stop fa-lrg"></span> @lang('buttons.stop')</button></td>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            <h4>{{ trans_choice('pages.subjects_assigned', 1) }}: <span id="subjectCount">{{ $expedition->subjectsCount }}</span></h4>
            <div class="table-responsive" id="jqtable">
                <input type="hidden" id="url" value="{{ URL::route('projects.grids.expeditions.show', [$expedition->project->id, $expedition->id]) }}">
                <input type="hidden" id="showCb" value="0">
                <input type="hidden" id="projectId" value="{{ $expedition->project->id }}">
                <input type="hidden" id="expeditionId" value="{{ $expedition->id }}">
                <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                <div id="pager"></div>
                <br />
                <button id="savestate" class="btn btn-default">Save Grid State</button>
                <button id="loadstate" class="btn btn-default">Load Grid State</button>
            </div>
        </div>
    </div>
    @include('frontend/layouts/jqgrid')
@stop