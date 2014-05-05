@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $project->title }}
@stop

{{-- Content --}}
@section('content')
<h4>{{ $project->title }}</h4>

<div class="well clearfix">
    <div class="col-md-8">
        <p><strong>@lang('pages.description'):</strong> {{ $project->description }} </p>
    </div>
    <div class="col-md-4">
        <p><em>@lang('pages.created'): {{ $project->created_at }}</em></p>
        <p><em>@lang('pages.updated'): {{ $project->updated_at }}</em></p>
        <button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ URL::route('addData', [$project->group_id, $project->id]) }}'">@lang('buttons.data')</button>
        <button class="btn btn-primary btn-xs" onClick="location.href='{{ action('ProjectsController@edit', array($project->group_id, $project->id)) }}'">@lang('buttons.edit')</button>
        <button class="btn btn-default btn-xs btn-danger action_confirm" href="{{ URL::route('groups.projects.destroy', [$project->group_id, $project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
    </div>
    <img src="<?= $project->logo->url('normal') ?>" >
</div>

<h4>{{ trans('pages.expeditions') }}:</h4>
<button class="btn btn-primary btn-xs" onClick="location.href='{{ URL::route('groups.projects.expeditions.create', [$project->group_id, $project->id]) }}'">@lang('buttons.create')</button>
<div class="table-responsive">
    <table class="table table-striped table-hover dataTable">
        <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Created</th>
            <th>Subjects</th>
            <th>Incomplete</th>
            <th>Complete</th>
            <th>Percent Complete</th>
            <th>options</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($expeditions as $expedition)
        <tr>
            <td>{{ $expedition->title }}</td>
            <td>{{ $expedition->description }}</td>
            <td>{{ $expedition->created_at }}</td>
            <td>{{ $expedition->total_subjects }}</td>
            <td>500</td>
            <td>300</td>
            <td>37.5%</td>
            <td>
                <button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@show', [$project->group_id, $project->id, $expedition->id]) }}'">@lang('buttons.view')</button>
                <button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@edit', [$project->group_id, $project->id, $expedition->id]) }}'">@lang('buttons.edit')</button>
                <button class="btn btn-default btn-xs btn-danger action_confirm" href="{{ action('ExpeditionsController@destroy', [$project->group_id, $project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@stop
