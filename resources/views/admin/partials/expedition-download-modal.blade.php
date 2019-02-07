<!-- Modal -->
<div class="modal fade" id="expedition-download-modal" tabindex="-2" role="dialog" aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ __('Expedition Downloads') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>
            <div class="text-center color-action" id="preparing-file" title="Preparing download" style="display: none;">
                We are preparing your download, please wait...
                <div class="ui-progressbar-value ui-corner-left ui-corner-right" style="width: 100%; height:22px; margin-top: 20px;"></div>
            </div>
            <div class="text-center color-action" id="error-file" title="Error" style="display: none;">
                There was a problem generating your file, please try again.
            </div>

            <div class="modal-body"></div>

            <div class="modal-footer text-center">
                <button type="button" class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">{{ __('EXIT') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->