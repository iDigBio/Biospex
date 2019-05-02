<!-- Modal -->
<div class="modal fade" id="process-modal" tabindex="-1" role="dialog" aria-labelledby="process-modal-label"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ __('pages.processes') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <p class="text-center color-action small">{{ __('pages.process_warning') }}</p>
                <div class="m-4">
                    <h4>{{ __('Ocr Processes') }}</h4>
                    <div id="ocr-html">{{ __('pages.processes_none.') }}</div>
                </div>
                <div class="m-4">
                    <div><h4>{{ __('pages.export') }} {{ __('pages.processes') }}</h4></div>
                    <div id="export-html">{{ __('pages.processes_none') }}</div>
                </div>
            </div>

            <div class="modal-footer text-center">
                <button type="button"
                        class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">{{ __('pages.exit') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->
