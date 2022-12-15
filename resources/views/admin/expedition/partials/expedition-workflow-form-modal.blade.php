<!-- Modal -->
<div class="modal fade" id="expedition-workflow-form-modal" tabindex="-2" role="dialog"
     aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ t('Zooniverse Workflow Id Form') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <div class="col-md-10">
                    <form id="workflowIdFrm" method="post"
                          action="{{ route('admin.expeditions.workflowId', [$expedition->project_id, $expedition->id]) }}"
                          role="form">
                        @csrf
                        <input type="hidden" name="expedition_id" value="{{ $expedition->id }}">
                        <div class="dfelx form-row align-items-center">
                            <div class="col-10">
                                <input type="text" name="panoptes_workflow_id" id="panoptes_workflow_id"
                                       class="form-control {{ ($errors->has('panoptes_workflow_id')) ? 'has-error' : '' }}"
                                       placeholder="{{ t('Enter Workflow Id after Expedition submitted to Zooniverse') }}"
                                       value="{{ old('panoptes_workflow_id', $expedition->panoptesProject->panoptes_workflow_id ?? '') }}" required />
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                        <div class="feedback"></div>
                    </form>
                </div>
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

