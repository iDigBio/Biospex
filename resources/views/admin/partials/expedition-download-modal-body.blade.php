@if($error)
    <div class="col-md-12 text-center">
        <h3>{{ t('You do not have sufficient permissions.') }}</h3>
    </div>
@else
    @foreach ($expedition->actors as $actor)
        <div class="col-md-12">
            <h4 class="float-left">{{ $actor->title }}</h4>
            {!! $expedition->present()->expedition_regenerate_export_btn !!}
            @if($actor->pivot->completed  && $actor->id === 2)
                {!! $actor->present()->reconcile_expert_review_btn !!}
            @endif
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                    <tr>
                        <th>{{ t('Download Type') }}</th>
                        <th>{{ t('Filename') }}</th>
                        <th>{{ t('File Size') }}</th>
                        <th>{{ t('Created') }}</th>
                        <th>{{ t('Updated') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($actor->downloads as $download)
                        @if ( ! empty($download) && GeneralHelper::downloadFileExists($download->type, $download->file))
                            <tr>
                                <td>{{ $download->present()->file_type }}</td>
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
                                            @if ($download->type === 'report')
                                                <a href="{{ route('admin.downloads.report', ['file' => base64_encode($download->file)]) }}"
                                                   data-hover="tooltip" target="_blank"
                                                   title="{{ t('Download') }} {{ $download->type }}">
                                                    <i class="fas fa-file-download fa-2x pl-2 ml-2"></i></a>
                                            @else
                                                <a href="{{ route('admin.downloads.download', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                                   data-hover="tooltip"
                                                   title="{{ t('Download') }} {{ $download->present()->file_type }}">
                                                    <i class="fas fa-file-download fa-2x"></i></a>
                                                @if ($download->type === 'summary')
                                                    <a href="{{ route('admin.downloads.summary', [$expedition->project->id, $expedition->id]) }}"
                                                       data-hover="tooltip" target="_blank"
                                                       title="{{ t('View') }} {{ $download->type }}">
                                                        <i class="fas fa-eye fa-2x pl-2 ml-2"></i></a>
                                                @endif
                                            @endif
                                        @endcan
                                    @else
                                        <a href="{{ route('admin.downloads.downloadTar', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                           data-hover="tooltip"
                                           title="{{ t('Download') }} {{ $download->type }} {{ t('archive') }}">
                                            <i class="fas fa-file-archive fa-2x"></i></a>
                                        <a href="{{ route('admin.downloads.batch', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                           data-hover="tooltip"
                                           title="{{ t('Download') }} {{ $download->type }} {{ t('batch') }}">
                                            <i class="fas fa-file-download fa-2x ml-2"></i></a>
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