@if($error)
    <div class="col-md-12 text-center">
        <h3>{{ t('You do not have sufficient permissions.') }}</h3>
    </div>
@else
    @foreach ($expedition->actors as $actor)
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm">
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
                                                   data-hover="tooltip"
                                                   data-placement="left"
                                                   target="_blank"
                                                   title="{{ t('Download') }} {{ $download->type }}">
                                                    <i class="fas fa-file-download fa-2x pl-2 ml-2"></i></a>
                                            @else
                                                <a href="{{ route('admin.downloads.download', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                                   data-hover="tooltip"
                                                   data-placement="left"
                                                   title="{{ t('Download') }} {{ $download->present()->file_type }}">
                                                    <i class="fas fa-file-download fa-2x"></i></a>
                                                @if ($download->type === 'summary')
                                                    <a href="{{ route('admin.downloads.summary', [$expedition->project->id, $expedition->id]) }}"
                                                       data-hover="tooltip" target="_blank"
                                                       data-placement="left"
                                                       title="{{ t('View') }} {{ $download->type }}">
                                                        <i class="fas fa-eye fa-2x pl-2 ml-2"></i></a>
                                                @endif
                                            @endif
                                        @endcan
                                    @else
                                        <a href="{{ $download->present()->export_download }}"
                                           class="mr-4"
                                           data-hover="tooltip"
                                           data-placement="left"
                                           data-original-title="{{ t('Download %s file', $download->type) }} ">
                                            <i class="fas fa-file-archive fa-2x"></i></a>
                                    <!--
                                        <a href="{{ route('admin.downloads.batch', [$expedition->project->id, $expedition->id, $download->id]) }}"
                                           class="prevent-default"
                                           data-method="get"
                                           data-confirm="confirmation"
                                           data-hover="tooltip"
                                           data-placement="left"
                                           data-original-title="{{ t('Download %s batches', $download->type) }}"
                                           data-content="{{ t('This action will split the Export file into several batch files that can be downloaded separately. You will be notified by email when the process is complete. Do you wish to continue?') }}">
                                            <i class="fas fa-file-download fa-2x"></i></a>
                                            -->
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