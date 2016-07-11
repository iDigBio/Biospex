@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show.title', $project, 'Explore') !!}
    <div class="jumbotron">
        <h3>{{ $project->title }}</h3>
        <p>{{ $project->description_short }}</p>

    </div>

    <div class="panel panel-primary">
        <div style="padding: 10px;">
            <p class="eyesright"><strong>@lang('pages.project_url')
                    :</strong> {!! link_to_route('home.get.project', $project->title, [$project->slug]) !!}</p>
            <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-sm" type="button"
                    onClick="location.href='{{ route('web.imports.import', [$project->id]) }}'"><span
                        class="fa fa-plus fa-lrg"></span> @lang('buttons.data')</button>
            <button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-sm" type="button"
                    onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'"><span
                        class="fa fa-search fa-lrg"></span> @lang('buttons.dataView')</button>
            <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-sm" type="button"
                    onClick="location.href='{{ route('web.projects.duplicate', [$project->id]) }}'"><span
                        class="fa fa-copy fa-lrg"></span> @lang('buttons.duplicate')</button>
            <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" type="button"
                    onClick="location.href='{{ route('web.projects.edit', [$project->id]) }}'"><span
                        class="fa fa-edit fa-lrg"></span> @lang('buttons.edit')</button>
            @can('delete', $project)
                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-sm"
                        data-method="delete"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('web.projects.delete', [$project->id]) }}"><span
                            class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')
                </button>
        @endcan
        <button title="@lang('buttons.advertiseTitle')" class="btn btn-success btn-sm" type="button"
                onClick="location.href='{{ route('web.advertises.index', [$project->id]) }}'"><span
                    class="fa fa-globe fa-lrg"></span> @lang('buttons.advertise')</button>
    </div>
    </div>

    <h3>{{ trans_choice('pages.subjects_assigned', $subjectAssignedCount) }}: <span
                id="subjectCount">{{ $subjectAssignedCount }}</span></h3>
    <div class="table-responsive" id="jqtable">
        <input type="hidden" id="url" value="{{ route('web.grids.explore', [$project->id]) }}">
        <input type="hidden" id="showCb" value="0">
        <input type="hidden" id="projectId" value="{{ $project->id }}">
        <input type="hidden" id="expeditionId" value="0">
        <table class="table table-bordered jgrid" id="jqGridExplore"></table>
        <div id="pager"></div>
        <br/>
        <button id="savestate" class="btn btn-default">Save Grid State</button>
        <button id="loadstate" class="btn btn-default">Load Grid State</button>
    </div>
    @include('frontend/layouts/jqgrid')
@stop

