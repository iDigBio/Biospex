<!-- Modal -->
<div class="modal fade" id="project-banner-modal" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ __('PROJECT PAGE BANNER') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div class="m-4">
                    <div class="project-banner" data-name="banner-desert.jpg" data-hover="tooltip"
                         title="{{ __('Click to select grass banner.') }}">
                        <img class="img-fluid" alt="{{ __('Desert Banner') }}"
                             src="{{ Storage::disk('public')->url('images/habitat-banners/banner-desert.jpg') }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-grass.jpg" data-hover="tooltip"
                         title="{{ __('Click to select grass banner.') }}">
                        <img class="img-fluid" alt="{{ __('Desert Banner') }}"
                             src="{{ Storage::disk('public')->url('images/habitat-banners/banner-grass.jpg') }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-meadow.jpg" data-hover="tooltip"
                         title="{{ __('Click to select meadow banner.') }}">
                        <img class="img-fluid" alt="{{ __('Desert Banner') }}"
                             src="{{ Storage::disk('public')->url('images/habitat-banners/banner-meadow.jpg') }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-mtn-flowers.jpg" data-hover="tooltip"
                         title="{{ __('Click to select mountain flowers banner.') }}">
                        <img class="img-fluid" alt="{{ __('Desert Banner') }}"
                             src="{{ Storage::disk('public')->url('images/habitat-banners/banner-mtn-flowers.jpg') }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-swamp.jpg" data-hover="tooltip"
                         title="{{ __('Click to select swamp banner.') }}">
                        <img class="img-fluid" alt="{{ __('Desert Banner') }}"
                             src="{{ Storage::disk('public')->url('images/habitat-banners/banner-swamp.jpg') }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-trees.jpg" data-hover="tooltip"
                         title="{{ __('Click to select trees banner.') }}">
                        <img class="img-fluid" alt="{{ __('Desert Banner') }}"
                             src="{{ Storage::disk('public')->url('images/habitat-banners/banner-trees.jpg') }}">
                    </div>
                </div>
            </div>

            <div class="modal-footer text-center">
                <button type="button" class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">{{ __('EXIT') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->
