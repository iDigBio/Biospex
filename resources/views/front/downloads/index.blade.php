@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('projects.expeditions.get.show.title', $expedition, 'Downloads') !!}

    <div class="jumbotron">
        <h2>{{ $expedition->title }}</h2>
        <p>{{ $expedition->description }}</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if ( ! $expedition->downloads->isEmpty())
                                <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.downloads.get.index', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-download fa-lrg"></span> @lang('buttons.download') </button>
                            @endif
                            <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-sm" type="button" onClick="location.href='{{ route('projects.get.import', [$expedition->project->id]) }}'"><span class="fa fa-plus fa-lrg"></span> @lang('buttons.data')</button>
                            <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.expeditions.get.duplicate', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-copy fa-lrg"></span> @lang('buttons.duplicate')</button>
                            <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" onClick="location.href='{{ route('projects.expeditions.get.edit', [$expedition->project->id, $expedition->id]) }}'"><span class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                            <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-sm" href="{{ route('projects.expeditions.delete.delete', [$expedition->project->id, $expedition->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
                        </div>
                    </div>
                </div>
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
                                    @if (File::exists(Config::get('config.nfn_export_dir') . '/' . $download->file))
                                        {{ human_file_size(File::size(Config::get('config.nfn_export_dir') . '/' . $download->file)) }}
                                    @else
                                        {{ human_file_size(mb_strlen($download->data, '8bit')) }}
                                    @endif
                                </td>
                                <td>{{ format_date($download->created_at, 'Y-m-d', $user->timezone) }}</td>
                                <td>{{ route('projects.expeditions.downloads.get.show', [$expedition->project->id, $expedition->id, $download->id]) }}</td>
                                <td><button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ route('projects.expeditions.downloads.get.show', [$expedition->project->id, $expedition->id, $download->id]) }}'"><span class="glyphicon glyphicon-floppy-save"></span> @lang('buttons.download') </button></td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                <br /><button title="Back to Expedition Details" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ route('projects.expeditions.get.show', [$expedition->project->id, $expedition->id]) }}'"><span class="glyphicon glyphicon-eye-open"></span> Return</button>
            </div>
        </div>
    </div>
@stop