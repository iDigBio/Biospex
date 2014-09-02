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
        <button class="btn btn-primary" onClick="location.href='{{ URL::route('projects.create') }}'">@lang('buttons.create')</button>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th></th>
                    <th>@lang('pages.title')</th>
                    <th>@lang('pages.description')</th>
                    <th>@lang('pages.group')</th>
                    <th class="nowrap">@lang('projects.project_options')</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($groups as $group)
                    @if ($group->name == 'Users' || $group->name == 'Admins')
                        @continue;
                    @endif
                    @foreach ($group->Projects as $project)
                    <tr>
                        <td><span id="collapse{{ $project->id }}" class="glyphicon glyphicon-folder-close pointer" data-toggle="collapse" data-target="#{{ $project->id }}"></span></td>
                        <td><a href="{{ URL::route('projects.show', [$project->id]) }}">{{ $project->title }}</a></td>
                        <td>{{ $project->description_short }} </td>
                        <td><a href="{{ URL::route('groups.show', [$group->id]) }}">{{ $group->name }}</a></td>
                        <td class="nowrap">
                            <button class="btn btn-info" type="button" onClick="location.href='{{ URL::route('projects.show', [$project->id]) }}'">@lang('buttons.view')</button>
                            <button class="btn btn-default" type="button" onClick="location.href='{{ URL::route('projects.data', [$project->id]) }}'">@lang('buttons.data')</button>
                            <button class="btn btn-primary" type="button" onClick="location.href='{{ URL::route('projects.duplicate', [$project->id]) }}'">@lang('buttons.duplicate')</button>
                            <button class="btn btn-warning" type="button" onClick="location.href='{{ URL::route('projects.edit', [$project->id]) }}'">@lang('buttons.edit')</button>
                            @if ($user->id == $group->user_id || $isSuperUser)
                            <button class="btn btn-default btn-danger action_confirm" href="{{ URL::route('projects.destroy', [$project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
                            @endif
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4">
                            <span id="{{ $project->id }}" class="collapse out"></span></td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop