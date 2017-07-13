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
        <button title="@lang('buttons.deleteTitle')"
                class="btn btn-danger btn-xs"
                data-method="delete"
                data-toggle="confirmation" data-placement="left"
                data-href="{{ route('web.expeditions.delete', [$project->id, $expedition->id]) }}"><span
                    class="fa fa-remove fa-lrg"></span> <!-- @lang('buttons.delete') -->
        </button>

        @if ( ! $expedition->downloads->isEmpty())
            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs"
                    type="button"
                    onClick="location.href='{{ route('web.downloads.index', [$project->id, $expedition->id]) }}'">
                <span class="fa fa-download fa-lrg"></span> <!-- @lang('buttons.download') -->
            </button>
        @endif
        @if(request()->user()->id === $project->group->user_id)
            <button title="@lang('buttons.transcriptsTitle')" class="btn btn-success btn-xs"
                    type="button"
                    onClick="location.href='{{ route('web.expeditions.transcripts', [$project->id, $expedition->id]) }}'">
                <span class="fa fa-file-text-o fa-lrg"></span> <!-- @lang('buttons.transcripts') -->
            </button>
        @endif
    </td>
</tr>