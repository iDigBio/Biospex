@if (download_file_exists($download->file, $download->type, $download->actor_id))
    <tr>
        <td>{{ $download->present()->file_type }}</td>
        <td>{{ $download->present()->file_type . '-' . $download->file }}</td>
        <td>
            {{ human_file_size(download_file_size($download->file, $download->type, $download->actor_id)) }}
        </td>
        <td>{{ format_date($download->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
        <td>{{ format_date($download->updated_at, 'Y-m-d', $user->profile->timezone) }}</td>
        <td class="d-flex justify-content-between">
            @if ($download->type != 'export')
                @can('isOwner', $expedition->project->group)
                    @if ($download->type === 'report')
                        <a href="{{ $download->present()->report_download }}"
                           data-hover="tooltip"
                           data-placement="left"
                           target="_blank"
                           title="{{ t('Download') }} {{ $download->type }}">
                            <i class="fas fa-file-download fa-2x pl-2 ml-2"></i></a>
                    @else
                        <a href="{{ $download->present()->download_type }}"
                           data-hover="tooltip"
                           data-browse="type"
                           data-placement="left"
                           title="{{ t('Download') }} {{ $download->present()->file_type }}">
                            <i class="fas fa-file-download fa-2x"></i></a>
                    @endif
                @endcan
            @else
                <a href="{{ $download->present()->export_download }}"
                   class="mr-4"
                   data-hover="tooltip"
                   data-placement="left"
                   data-original-title="{{ t('Download %s file', $download->type) }} ">
                    <i class="fas fa-file-download fa-2x"></i></a>

                @if($actor->id == config('zooniverse.actor_id'))
                    <a href="{{ route('admin.downloads.batch', [$expedition->project->id, $expedition->id, $download->id]) }}"
                       class="prevent-default"
                       data-method="get"
                       data-confirm="confirmation"
                       data-hover="tooltip"
                       data-placement="left"
                       data-original-title="{{ t('Download %s batches', $download->type) }}"
                       data-content="{{ t('This action will split the Export file into several batch files that can be downloaded separately. You will be notified by email when the process is complete. Do you wish to continue?') }}">
                        <i class="fas fa-file-archive fa-2x" aria-hidden="true"></i></a>
                @endif

            @endif
        </td>
    </tr>
@endif
