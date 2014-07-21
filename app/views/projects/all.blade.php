@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
    <h3>{{ trans('projects.projects') }}</h3>
    </div>
    @foreach ($groupProjects as $key => $projects)
    <div class="col-md-10 col-md-offset-1">
        <h4>{{ trans('groups.group') }}: {{ $groupNames[$key] }}</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th></th>
                    <th>@lang('pages.title')</th>
                    <th>@lang('pages.description')</th>
                    <th class="nowrap">@lang('projects.project_options')</th>
                    <th><button class="btn btn-primary" onClick="location.href='{{ URL::route('groups.projects.create', [$key]) }}'">@lang('buttons.create')</button></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($projects as $key => $project)
                <tr>
                    <td><span id="collapse{{ $project->id }}" class="glyphicon glyphicon-folder-close pointer" data-toggle="collapse" data-target="#{{ $project->id }}"></span></td>
                    <td><a href="{{ URL::route('groups.projects.show', [$project->group_id, $project->id]) }}">{{ $project->title }}</a></td>
                    <td>{{ $project->description }} </td>
                    <td class="nowrap">
                        <button class="btn btn-info" type="button" onClick="location.href='{{ URL::route('groups.projects.show', [$project->group_id, $project->id]) }}'">@lang('buttons.view')</button>
                        <button class="btn btn-default" type="button" onClick="location.href='{{ URL::route('addData', [$project->group_id, $project->id]) }}'">@lang('buttons.data')</button>
                        <button class="btn btn-primary" type="button" onClick="location.href='{{ URL::route('project-dup', [$project->group_id, $project->id]) }}'">@lang('buttons.duplicate')</button>
                        <button class="btn btn-warning" type="button" onClick="location.href='{{ URL::route('groups.projects.edit', [$project->group_id, $project->id]) }}'">@lang('buttons.edit')</button>
                        <button class="btn btn-default btn-danger action_confirm" href="{{ URL::route('groups.projects.destroy', [$project->group_id, $project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="4">
                        <span title="{{ $project->group_id }}" id="{{ $project->id }}" class="collapse out"></span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@stop