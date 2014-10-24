@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.expeditions.inside', $expedition) }}

    <div class="jumbotron">
    <h4>{{ trans('expeditions.expedition') }}:</h4>
    <h2>{{ $expedition->title }}</h2>
    <p>{{ $expedition->description }}</p>
    </div>
    
    <div class="alert alert-info">
        <p><strong>{{ trans('expeditions.download_ready') }}</strong></p>
    </div>

<div class="table-responsive">
    <table class="table table-striped table-hover dataTable">
        <thead>
        <tr>
            <th>{{ trans('pages.actor') }}</th>
            <th>{{ trans('pages.filename') }}</th>
            <th>{{ trans('pages.created') }}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($expedition->download as $download)
        <tr>
            <td>{{ $download->workflow->title }}</td>
            <td>{{ $download->file }}</td>
            <td>{{ $download->created_at }}</td>
            <td>
            @if (file_exists(Config::get('config.dataDir') . '/' . $download->file))
                <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@file', [$expedition->project->id, $expedition->id, $download->id]) }}'"><span class="glyphicon glyphicon-floppy-save"></span> @lang('buttons.download') </button>
            @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <br /><button title="Back to Expedition Details" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@process', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-eye-open"></span> Return</button>
</div>

@stop