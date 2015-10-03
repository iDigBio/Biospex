@extends('front.layouts.default')
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
        <p>{{ $project->description_short }}</p>

    </div>

    <div class="panel panel-default">
        <div style="padding: 10px;">
            <p class="eyesright"><strong>@lang('pages.project_url'):</strong> {{ HTML::linkAction('HomeController@project', $project->title, [$project->slug]) }} </p>
            <button title="@lang('buttons.dataTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ URL::route('projects.import', [$project->id]) }}'"><span class="glyphicon glyphicon-plus-sign"></span> @lang('buttons.data')</button>
            <button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ URL::route('projects.subjects.show', [$project->id]) }}'"><span class="glyphicon glyphicon-search"></span> @lang('buttons.dataView')</button>
            <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ URL::route('projects.duplicate', [$project->id]) }}'"><span class="glyphicon glyphicon-share-alt"></span> @lang('buttons.duplicate')</button>
            <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button" onClick="location.href='{{ URL::route('projects.edit', [$project->id]) }}'"><span class="glyphicon glyphicon-cog"></span> @lang('buttons.edit')</button>
            @if ($isOwner)
                <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ URL::route('projects.destroy', [$project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button></td>
            @endif
            <button title="@lang('buttons.advertiseTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ URL::route('projects.advertise', [$project->id]) }}'"><span class="glyphicon glyphicon-globe"></span> @lang('buttons.advertise')</button>
        </div>
    </div>

    <hr />

    <h3>{{ trans('pages.subjects') }}:</h3>
    <div class="table-responsive" id="jqtable">
        <input type="hidden" id="projectId" value="{{ $project->id }}">
        <input type="hidden" id="expeditionId" value="0">
        <table id="list"></table>
        <div id="pager"></div>
    </div>
@stop