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
            <th>{{ trans('pages.id') }}</th>
            <th>{{ trans('pages.filename') }}</th>
            <th>{{ trans('pages.filesize') }}</th>
            <th>{{ trans('pages.created') }}</th>
            <th>{{ trans('pages.downloadurl') }}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($expedition->downloads as $download)
            @if ( ! empty($download))
            <tr>
                <td>{{ $download->actor->title }}</td>
                <td>{{ $download->id }}</td>
                <td>{{ $download->file }}</td>
                <td>
                    @if (File::exists(Config::get('config.nfnExportDir') . '/' . $download->file))
                        {{ Helper::humanFileSize(File::size(Config::get('config.nfnExportDir') . '/' . $download->file)) }}
                    @else
                        {{ Helper::humanFileSize(mb_strlen($download->data, '8bit')) }}
                    @endif
                </td>
                <td>{{ Helper::formatDate($download->created_at, 'Y-m-d', $user->timezone) }}</td>
                <td>{{ link_to_route('apidownloads.get.show', null, [$download->id]) }}</td>
                <td><button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ route('apidownloads.get.show', [$download->id]) }}'"><span class="glyphicon glyphicon-floppy-save"></span> @lang('buttons.download') </button></td>
            </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    <br /><button title="Back to Expedition Details" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ action('ExpeditionsController@process', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-eye-open"></span> Return</button>
</div>

@stop