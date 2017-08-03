@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.expeditions.show.title', $expedition, 'Downloads') !!}

    <div class="jumbotron">
        <h2>{{ $expedition->title }} @lang('expeditions.transcriptions')</h2>
        <p>{{ $expedition->description }}</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <button title="Back to Expedition Details" class="btn btn-info btn-xs" type="button"
                            onClick="location.href='{{ route('web.expeditions.show', [$expedition->project->id, $expedition->id]) }}'">
                        <span class="glyphicon glyphicon-eye-open"></span> Return
                    </button>
                </div>
            </div>
        </div>
    </div>

    @foreach ($expedition->actors as $actor)
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $actor->title }}</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover dataTable">
                            <thead>
                            <tr>
                                <th>{{ trans('pages.download_type') }}</th>
                                <th>{{ trans('pages.filename') }}</th>
                                <th>{{ trans('pages.filesize') }}</th>
                                <th>{{ trans('pages.created') }}</th>
                                <th>{{ trans('pages.updated') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($actor->downloads as $download)
                                @if ( ! empty($download))
                                    <tr>
                                        <td>{{ $download->type }}</td>
                                        <td>{{ $download->file }}</td>
                                        <td>
                                            @if (File::exists(Config::get('config.nfn_export_dir') . '/' . $download->file))
                                                {{ human_file_size(File::size(Config::get('config.nfn_export_dir') . '/' . $download->file)) }}
                                            @else
                                                {{ human_file_size(mb_strlen($download->data, '8bit')) }}
                                            @endif
                                        </td>
                                        <td>{{ format_date($download->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
                                        <td>{{ format_date($download->updated_at, 'Y-m-d', $user->profile->timezone) }}</td>
                                        <td>
                                            <button title="@lang('buttons.downloadTitle')"
                                                    class="btn btn-success btn-xs"
                                                    type="button"
                                                    onClick="location.href='{{ route('projects.expeditions.downloads.get.show', [$expedition->project->id, $expedition->id, $download->id]) }}'">
                                                <span class="fa fa-download"></span> @lang('buttons.download')
                                            </button>
                                            @if ($download->type === 'export')
                                            <button title="@lang('buttons.regenerateDownload')"
                                                    class="btn btn-success btn-xs" type="button"
                                                    onClick="location.href='{{ route('web.downloads.regenerate', [$expedition->project->id, $expedition->id]) }}'">
                                                <span class="fa fa-refresh"></span> @lang('buttons.regenerateDownload')
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop