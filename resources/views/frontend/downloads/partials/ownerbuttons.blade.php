@can('isOwner', $expedition->project->group)
    <button title="@lang('pages.downloadTitle')"
            class="btn btn-success btn-xs"
            type="button"
            onClick="location.href='{{ route('projects.expeditions.downloads.get.show', [$expedition->project->id, $expedition->id, $download->id]) }}'">
        <span class="fa fa-download"></span> @lang('pages.download')
    </button>
    @if ($download->type === 'summary')
        <button title="@lang('pages.summaryTitle')" class="btn btn-primary btn-xs" type="button"
                onClick="window.open('{{ route('webauth.downloads.summary', [$expedition->project->id, $expedition->id]) }}', '_blank')">
            <span class="fa fa-eye fa-lrg"></span> @lang('pages.summary')
        </button>
    @endif
@endcan