<button title="@lang('buttons.downloadTitle')"
        class="btn btn-success btn-xs"
        type="button"
        onClick="location.href='{{ route('projects.expeditions.downloads.get.show', [$expedition->project->id, $expedition->id, $download->id]) }}'">
    <span class="fa fa-download"></span> @lang('buttons.download')
</button>
@if ($download->type === 'export')
    <button title="@lang('buttons.regenerateDownload')"
            class="btn btn-success btn-xs" type="button"
            onClick="location.href='{{ route('webauth.downloads.regenerate', [$expedition->project->id, $expedition->id]) }}'">
        <span class="fa fa-refresh"></span> @lang('buttons.regenerateDownload')
    </button>
@endif