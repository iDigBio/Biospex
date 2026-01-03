<div wire:ignore.self class="modal fade" id="ocr-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ t('OCR Content') }}</h2></div>
                <div>
                    {{-- Use data-dismiss="modal" for Bootstrap 4 --}}
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                @livewire('ocr-modal')
            </div>
            <div class="modal-footer text-center">
                {{-- Use data-dismiss="modal" for Bootstrap 4 --}}
                <button type="button"
                        class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">
                    {{ t('Exit') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('open-bs-modal', () => {
        $('#ocr-modal').modal('show');
    });

    window.addEventListener('close-ocr-modal', () => {
        $('#ocr-modal').modal('hide');
    });
</script>