<button title="@lang('pages.downloadTitle')"
        class="btn btn-success btn-xs"
        type="button"
        onClick="location.href='{{ route('projects.expeditions.downloads.get.show', [$expedition->project->id, $expedition->id, $download->id]) }}'">
    <span class="fa fa-download"></span> @lang('pages.download')
</button>
@if ($download->type === 'export')
    <button title="@lang('pages.regenerateDownload')"
            class="btn btn-success btn-xs" type="button"
            onClick="location.href='{{ route('webauth.downloads.regenerate', [$expedition->project->id, $expedition->id]) }}'">
        <span class="fa fa-refresh"></span> @lang('pages.regenerateDownload')
    </button>
@endif