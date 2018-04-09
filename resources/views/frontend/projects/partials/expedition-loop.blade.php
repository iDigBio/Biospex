<tr>
    <td>{{ $expedition->title }}</td>
    <td>{{ $expedition->description }}</td>
    <td>{{ DateHelper::formatDate($expedition->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td>{{ $expedition->stat->local_subject_count }}</td>
    <td>{{ $expedition->stat->subject_count }}</td>
    @if( ! $expedition->actors->isEmpty())
        <td>{{ $expedition->stat->transcriptions_total }}</td>
        <td>{{ $expedition->stat->transcriptions_completed }}</td>
        <td class="nowrap">
                    <span class="complete">
                        <span class="complete{{ GeneralHelper::roundUpToAnyFive($expedition->stat->percent_completed) }}">&nbsp;</span>
                    </span> {{ $expedition->stat->percent_completed }}%
        </td>
    @else
        <td class="nowrap" colspan="3">{{ trans('messages.processing_not_started') }}</td>
    @endif
    <td class="fit">
        <button title="@lang('pages.viewTitle')" class="btn btn-primary btn-xs" type="button"
                onClick="location.href='{{ route('webauth.expeditions.show', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-eye fa-lrg"></span> <!-- @lang('pages.view') --></button>
        <button title="@lang('pages.duplicateTitle')" class="btn btn-success btn-xs"
                type="button"
                onClick="location.href='{{ route('webauth.expeditions.duplicate', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-copy fa-lrg"></span> <!-- @lang('pages.duplicate') --></button>
        <button title="@lang('pages.editTitle')" class="btn btn-warning btn-xs" type="button"
                onClick="location.href='{{ route('webauth.expeditions.edit', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-cog fa-lrg"></span> <!-- @lang('pages.edit') --></button>
        <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                data-href="{{ route('webauth.expeditions.delete', [$project->id, $expedition->id]) }}"
                data-method="delete"
                data-toggle="confirmation"
                data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                data-btn-ok-class="btn-success"
                data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                data-btn-cancel-class="btn-danger"
                data-title="Continue action?" data-content="This will trash the item">
            <span class="fa fa-remove fa-lrg"></span> <!-- @lang('pages.delete') -->
        </button>


        @if ( ! $expedition->downloads->isEmpty())
            <button title="@lang('pages.downloadTitle')" class="btn btn-success btn-xs"
                    type="button"
                    onClick="location.href='{{ route('webauth.downloads.index', [$project->id, $expedition->id]) }}'">
                <span class="fa fa-download fa-lrg"></span> <!-- @lang('pages.download') -->
            </button>
        @endif
    </td>
</tr>