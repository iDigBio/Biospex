@if($error)
    <div class="col-md-12 text-center">
        <h3>{{ __('pages.insufficient_permissions') }}</h3>
    </div>
@else
    @foreach ($expedition->actors as $actor)
        <div class="col-md-12">
            <h3>{{ $actor->title }}
                <a class="float-right mr-4"
                   href="{{ route('admin.downloads.regenerate', [$expedition->project->id, $expedition->id]) }}"
                   data-hover="tooltip"
                   title="{{ __('pages.regenerate_export') }}">
                    <i class="fas fa-redo-alt"></i></a>
            </h3>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
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
                        @if ( ! empty($download) && GeneralHelper::downloadFileExists($download->type, $download->file))
                            <tr>
                                <td>{{ $download->type }}</td>
                                <td>{{ $download->file }}</td>
                                <td>
                                    @if (GeneralHelper::downloadFileExists($download->type, $download->file))
                                        {{ GeneralHelper::humanFileSize(GeneralHelper::downloadFileSize($download->type, $download->file)) }}
                                    @else
                                        {{ GeneralHelper::humanFileSize(mb_strlen($download->data, '8bit')) }}
                                    @endif
                                </td>
                                <td>{{ DateHelper::formatDate($download->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
                                <td>{{ DateHelper::formatDate($download->updated_at, 'Y-m-d', $user->profile->timezone) }}</td>
                                <td class="d-flex justify-content-between">
                                    @if ($download->type != 'export')
                                        @can('isOwner', $expedition->project->group)
                                            <a href="{{ route('admin.downloads.download', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                               data-hover="tooltip"
                                               title="{{ __('pages.download') }} {{ $download->type }}">
                                                <i class="fas fa-file-download fa-2x"></i></a>
                                            @if ($download->type === 'summary')
                                                <a href="{{ route('admin.downloads.summary', [$expedition->project->id, $expedition->id]) }}"
                                                   data-hover="tooltip" target="_blank"
                                                   title="{{ __('pages.view') }} {{ $download->type }}">
                                                    <i class="fas fa-eye fa-2x"></i></a>
                                            @endif
                                        @endcan
                                    @else
                                        <a href="{{ route('admin.downloads.download', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                           data-hover="tooltip"
                                           title="{{ __('pages.download') }} {{ $download->type }}">
                                            <i class="fas fa-file-download fa-2x"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif