@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('webauth.expeditions.show', $expedition) !!}
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
                                <button title="@lang('pages.downloadTitle')" class="btn btn-success btn-sm"
                                        type="button"
                                        onClick="location.href='{{ route('webauth.downloads.index', [$expedition->project->id, $expedition->id]) }}'">
                                    <span class="fa fa-download fa-lrg"></span> @lang('pages.download') </button>
                            @endif
                            <button title="@lang('pages.duplicateTitle')" class="btn btn-success btn-sm" type="button"
                                    onClick="location.href='{{ route('webauth.expeditions.duplicate', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-copy fa-lrg"></span> @lang('pages.duplicate')</button>
                            <button title="@lang('pages.editTitle')" class="btn btn-warning btn-sm"
                                    onClick="location.href='{{ route('webauth.expeditions.edit', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-cog fa-lrg"></span> @lang('pages.edit')</button>

                                @can('isOwner', $expedition->project->group->getWrappedObject())
                            <button class="btn btn-sm btn-danger" title="@lang('pages.deleteTitle')"
                                    data-href="{{ route('webauth.expeditions.delete', [$expedition->project->id, $expedition->id]) }}"
                                    data-method="delete"
                                    data-toggle="confirmation"
                                    data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                                    data-btn-ok-class="btn-success"
                                    data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                                    data-btn-cancel-class="btn-danger"
                                    data-title="Continue action?" data-content="This will delete the item">
                                <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                            </button>
                                @endcan

                        </div>
                        <div class="col-md-2">
                            <button title="@lang('pages.ocrTitle')" class="btn btn-success btn-sm" type="button"
                                    {{ $btnDisable ? '' : 'disabled' }}
                                    onClick="location.href='{{ route('webauth.expeditions.ocr', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-repeat fa-lrg"></span>
                                {{ $btnDisable ? trans('pages.ocr') : trans('pages.ocrDisabled') }}
                            </button>

                        </div>
                        <div class="col-md-2">
                            @if (null === $expedition->workflowManager || $expedition->workflowManager->stopped === 1)
                                <button title="@lang('pages.processTitle')" class="btn btn-success btn-sm" type="button"
                                        {{ $expedition->stat->subject_count === 0 ? 'disabled' : '' }}
                                        onClick="location.href='{{ route('webauth.expeditions.process', [$expedition->project->id, $expedition->id]) }}'">
                                    <span class="fa fa-play fa-lrg"></span> @lang('pages.process')</button>
                            @else
                                <button class="btn btn-sm btn-danger" title="@lang('pages.stopTitle')"
                                        data-href="{{ route('webauth.expeditions.stop', [$expedition->project->id, $expedition->id]) }}"
                                        data-method="delete"
                                        data-toggle="confirmation"
                                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                                        data-btn-ok-class="btn-success"
                                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                                        data-btn-cancel-class="btn-danger"
                                        data-title="Continue action?" data-content="This will stop the process">
                                    <span class="fa fa-remove fa-lrg"></span> @lang('pages.stop')
                                </button>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h4>{{ trans_choice('pages.subjects_assigned', 1) }}: {{ $expedition->stat->subject_count }}</h4>
            <div class="table-responsive" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                <div id="pager"></div>
                <br/>
                {!! Form::hidden('subjectIds', '', ['name' => 'subjectIds', 'id' => 'subjectIds']) !!}
                <button id="savestate" class="btn btn-default">Save Grid State</button>
                <button id="loadstate" class="btn btn-default">Load Grid State</button>
            </div>
        </div>
    </div>
    @include('frontend/layouts/jqgrid')
@stop