@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
<h4>@lang('projects.projects'):</h4>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <th></th>
                <th>@lang('pages.title')</th>
                <th>@lang('pages.description')</th>
                <th>@lang('pages.options')</th>
                </thead>
                <tbody>
                @foreach ($projects as $project)
                <tr>
                    <td><span id="collapse{{ $project->id }}" class="glyphicon glyphicon-folder-close pointer" data-toggle="collapse" data-target="#{{ $project->id }}"></span></td>
                    <td><a href="{{ action('ProjectsController@show', array($project->id)) }}">{{ $project->title }}</a></td>
                    <td>{{ $project->description }} </td>
                    <td>
                        <button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('ProjectsController@edit', array($project->id)) }}'">@lang('buttons.edit')</button>
                        <button class="btn btn-default btn-danger action_confirm btn-xs" href="{{ action('ProjectsController@destroy', array($project->id)) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
                </tr>
                <tr id="{{ $project->id }}" class="collapse out">
                    <td id="expeditions{{ $project->id }}" colspan="4"></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
