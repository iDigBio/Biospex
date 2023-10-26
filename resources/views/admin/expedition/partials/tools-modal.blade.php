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
                <div class="col-md-12 text-center">
                    <div class="btn-group-lg btn-group-vertical mb-2 align-items-center">
                    @if($expedition->project->ocrQueue->isEmpty())
                        <h4>{{ t('OCR') }}</h4>
                        {!! $expedition->present()->expedition_ocr_btn !!}
                    @endif
                    @php($nfnComplete = false)
                    @foreach ($expedition->actors as $actor)
                        @if($actor->id == config('config.nfnActorId'))
                            @php($nfnComplete = $actor->pivot->state === 3)
                            @include('admin.expedition.partials.nfn-btns')
                        @endif
                        @if($actor->id == config('config.geolocate.actor_id') && $nfnComplete)
                            @include('admin.expedition.partials.geolocate-btns')
                        @endif
                    @endforeach
                    </div>
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