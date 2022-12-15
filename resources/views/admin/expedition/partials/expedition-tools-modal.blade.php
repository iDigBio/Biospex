<!-- Modal -->
<div class="modal fade" id="expedition-tools-modal" tabindex="-2" role="dialog" aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ t('Expedition Tools') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                @foreach ($expedition->actors as $actor)
                    <div class="col-md-12 text-center">
                        <h4>{{ $actor->title }}</h4>
                        <div class="btn-group-lg btn-group-vertical mb-2">
                            @if($expedition->project->ocrQueue->isEmpty())
                                {!! $expedition->present()->expedition_ocr_btn !!}
                            @endif
                            @if($expedition->stat->local_subject_count > 0)
                                {!! $expedition->present()->expedition_export_btn !!}
                            @endif
                            @if(\App\Facades\GeneralHelper::exportFileCheck($expedition))
                                {!! $expedition->present()->expedition_workflow_btn  !!}
                                @if(\App\Facades\GeneralHelper::checkPanoptesWorkflow($expedition))
                                    @if ($expedition->workflowManager === null || $expedition->workflowManager->stopped === 1)
                                        {!!
                                        $expedition->stat->local_subject_count === 0 ? '' :
                                            $expedition->present()->expedition_process_start_btn
                                        !!}
                                    @else
                                        {!! $expedition->present()->expedition_process_stop_btn !!}
                                    @endif
                                @endif
                            @endif
                            @if($actor->pivot->completed  && $actor->id === 2)
                                {!! $actor->present()->reconcile_expert_review_btn !!}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">{{ t('Exit') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->