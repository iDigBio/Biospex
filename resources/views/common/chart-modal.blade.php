<!-- Modal -->
<div class="modal fade" id="chart-modal" tabindex="-1" role="dialog" aria-labelledby="chart-modal-label"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">Some Title</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body" data-href="{{ $route }}">

            </div>

            <div class="modal-footer text-center">
                <button type="button"
                        class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">{{ __('pages.exit') }}</button>
            </div>
            <div class="hide" id="state" data-state=""></div>
            <div class="hide" id="statevar" data-statevar=""></div>
        </div>
    </div>
</div>
<!-- end modal -->
