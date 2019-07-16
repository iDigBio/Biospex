<div class="row">
    <div class="col-sm-10 mx-auto">
        <div class="jumbotron box-shadow pt-2 pb-5 my-5 p-sm-5">
            <h1 class="text-center content-header text-uppercase">{{ $expedition->title }}</h1>
            <div class="col-12">
                <div class="d-flex justify-content-between mt-4 mb-3">
                    {!! $expedition->project->present()->project_admin_icon_lrg !!}
                    {!! $expedition->present()->expedition_show_icon_lrg !!}
                    @if($expedition->downloads->isNotEmpty())
                        {!! $expedition->present()->expedition_download_icon_lrg !!}
                    @endif
                    @if($expedition->project->ocrQueue->isEmpty())
                        {!! $expedition->present()->expedition_ocr_icon_lrg !!}
                    @endif
                    @if ($expedition->workflowManager === null || $expedition->workflowManager->stopped === 1)
                        {!!
                        $expedition->stat->local_subject_count === 0 ? '' :
                            $expedition->present()->expedition_process_start_lrg
                        !!}
                    @else
                        {!! $expedition->present()->expedition_process_stop_lrg !!}
                    @endif
                    {!! $expedition->present()->expedition_edit_icon_lrg !!}
                    {!! $expedition->present()->expedition_clone_icon_lrg !!}
                    @can('isOwner', $expedition->project->group)
                        {!! $expedition->present()->expedition_delete_icon_lrg !!}
                    @endcan
                </div>
                <hr class="header mx-auto" style="width:300px;">
                <div class="d-flex justify-content-between mt-4">
                    <span class="text">{{ __('pages.biospex') }} {{ __('pages.subjects') }} {{ $expedition->stat->local_subject_count }}</span>
                    <span class="text">{{ __('pages.nfn') }} {{ __('pages.subjects') }} {{ $expedition->stat->subject_count }}</span>
                    <span class="text">{{ __('pages.transcription') }} {{ __('pages.goal') }} {{ $expedition->stat->transcriptions_total }}</span>
                    <span class="text">{{ __('pages.transcriptions') }} {{ __('pages.completed') }} {{ $expedition->stat->percent_completed }}%</span>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.expedition-download-modal')
</div>
