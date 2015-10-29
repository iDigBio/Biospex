@extends('layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.inside', $project) }}
<div class="jumbotron">
    <h4>Project:</h4>
    <h2>{{ $project->title }}</h2>
    <p>@lang('pages.advertise_title')</p>
    <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ URL::route('projects.advertiseDownload', [$project->id]) }}'"><span class="glyphicon glyphicon-floppy-save"></span> @lang('buttons.download') </button>
</div>
<h3>Fields in File</h3>
<div class="table-responsive">
    <table class="table table-striped table-hover dataTable">
        <thead>
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
        </thead>
        <tbody>
        @foreach (stripslashes($project->advertise) as $field => $value)
            <tr>
                <td>{{ $field }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@stop
