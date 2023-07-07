<!-- Modal -->
<div class="modal fade" id="wedigbio-rate-modal" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div>
                    <h2 class="color-action">{{ t('WEDIGBIO RATE') }}</h2>
                    <span class="need-review pl-2">{{ t('updates every 5 minutes') }}</span>
                </div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div class="jumbotron box-shadow m-5">
                    <div id="weDigBioRateChartDiv" class="d-flex justify-content-center" style="width:100%; height: 500px"></div>
                </div>
            </div>

            <x-buttons.modal-dismiss />
        </div>
    </div>
</div>
<!-- end modal -->
