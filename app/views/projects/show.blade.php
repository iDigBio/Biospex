@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $project->title }}
@stop

{{-- Content --}}
@section('content')

        <ul class="breadcrumb">
        <li>Group: {{ $project->group->name }}</li>
        <li>@lang('pages.created'): {{ $project->created_at }}</li>
        <li>@lang('pages.updated'): {{ $project->updated_at }}</li>
        </ul>
        
        <div class="jumbotron">
        <h4>Project:</h4>
        <h2>{{ $project->title }}</h2>
        <p>{{ $project->description_short }}</p>
        
        </div>

<div class="panel panel-default">
    <div style="padding: 10px;">
    <p class="eyesright"><strong>@lang('pages.project_url'):</strong> {{ HTML::linkAction('HomeController@project', $project->title, [$project->slug]) }} </p>
    <button title="@lang('buttons.dataTitle')" class="btn btn-default btn-xs" type="button" onClick="location.href='{{ URL::route('projects.data', [$project->id]) }}'"><span class="glyphicon glyphicon-plus-sign"></span> @lang('buttons.data')</button>
    <button title="@lang('buttons.duplicateTitle')" class="btn btn-primary btn-xs" type="button" onClick="location.href='{{ URL::route('projects.duplicate', [$project->id]) }}'"><span class="glyphicon glyphicon-share-alt"></span> @lang('buttons.duplicate')</button>
    <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button" onClick="location.href='{{ URL::route('projects.edit', [$project->id]) }}'"><span class="glyphicon glyphicon-cog"></span> @lang('buttons.edit')</button>
    @if ($isOwner)
    <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ URL::route('projects.destroy', [$project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button></td>
    @endif
    <button title="@lang('buttons.advertiseTitle')" class="btn btn-success btn-xs" type="button" data-toggle="modal" data-target="#basicModal"><span class="glyphicon glyphicon-globe"></span> @lang('buttons.advertise')</button>
    </div>
</div>

<hr />

<h3>{{ trans('pages.expeditions') }}:</h3>
<button class="btn btn-success" title="@lang('buttons.createTitleE')" onClick="location.href='{{ URL::route('projects.expeditions.create', [$project->id]) }}'"><span class="glyphicon glyphicon-plus"></span> @lang('buttons.create')</button>
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
            <th>Options</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($project->expedition as $expedition)
        <tr>
            <td>{{ $expedition->title }}</td>
            <td>{{ $expedition->description_short }}</td>
            <td>{{ $expedition->created_at }}</td>
            <td>{{ $expedition->total_subjects }}</td>
            <td>500</td>
            <td>300</td>
            <td><span class="complete"><span class="complete85">&nbsp;</span></span> 85%</td>
            <td class="nowrap">
                <button title="@lang('buttons.viewTitle')" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@show', [$project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-eye-open"></span> <!-- @lang('buttons.view') --></button>
                <button title="@lang('buttons.duplicateTitle')" class="btn btn-primary btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@duplicate', [$project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-share-alt"></span> <!-- @lang('buttons.duplicate') --></button>
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@edit', [$project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-cog"></span> <!-- @lang('buttons.edit') --></button>
                <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ action('ExpeditionsController@destroy', [$project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> <!-- @lang('buttons.delete') --></button>
                @if ( ! $expedition->download->isEmpty())
                <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@download', [$project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-floppy-save"></span> <!-- @lang('buttons.download') --></button>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">@lang('pages.advertise')</h4>
            </div>
            <div class="modal-body">
                @lang('pages.advertise_modal')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('pages.close')</button>
        </div>
    </div>
  </div>
</div>
@stop
