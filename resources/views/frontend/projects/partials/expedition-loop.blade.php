<tr>
    <td>{{ $expedition->title }}</td>
    <td>{{ $expedition->description }}</td>
    <td>{{ format_date($expedition->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td>{{ $expedition->subjectsCount }}</td>
    @if( ! $expedition->actors->isEmpty())
        <td>{{ $expedition->stat->transcriptions_total }}</td>
        <td>{{ $expedition->stat->transcriptions_completed }}</td>
        <td class="nowrap">
                    <span class="complete">
                        <span class="complete{{ round_up_to_any_five($expedition->stat->percent_completed) }}">&nbsp;</span>
                    </span> {{ $expedition->stat->percent_completed }}%
        </td>
    @else
        <td class="nowrap" colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
    @endif
    <td class="buttons-xs">
        <button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-xs" type="button"
                onClick="location.href='{{ route('web.expeditions.show', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-eye fa-lrg"></span> <!-- @lang('buttons.view') --></button>
        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs"
                type="button"
                onClick="location.href='{{ route('web.expeditions.duplicate', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-copy fa-lrg"></span> <!-- @lang('buttons.duplicate') --></button>
        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                onClick="location.href='{{ route('web.expeditions.edit', [$project->id, $expedition->id]) }}'">
            <span class="fa fa-cog fa-lrg"></span> <!-- @lang('buttons.edit') --></button>
        <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                data-href="{{ route('web.expeditions.delete', [$project->id, $expedition->id]) }}"
                data-method="delete"
                data-toggle="confirmation"
                data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                data-btn-ok-class="btn-success"
                data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                data-btn-cancel-class="btn-danger"
                data-title="Continue action?" data-content="This will trash the item">
            <span class="fa fa-remove fa-lrg"></span> <!-- @lang('buttons.delete') -->
        </button>


        @if ( ! $expedition->downloads->isEmpty())
            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs"
                    type="button"
                    onClick="location.href='{{ route('web.downloads.index', [$project->id, $expedition->id]) }}'">
                <span class="fa fa-download fa-lrg"></span> <!-- @lang('buttons.download') -->
            </button>
        @endif
    </td>
</tr>