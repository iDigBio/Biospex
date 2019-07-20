<!-- Modal -->
<div class="modal fade" id="project-banner-modal" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action text-uppercase">{{ __('pages.project') }} {{ __('pages.project') }} {{ __('pages.banner') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div class="m-4">
                    <div class="project-banner" data-name="banner-desert.jpg" data-hover="tooltip"
                         title="{{ __('pages.banner_click') }}">
                        <img class="img-fluid" alt="{{ __('pages.banner_desert') }}"
                             src="{{ '/images/habitat-banners/banner-desert.jpg' }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-grass.jpg" data-hover="tooltip"
                         title="{{ __('pages.banner_click') }}">
                        <img class="img-fluid" alt="{{ __('pages.banner_grass') }}"
                             src="{{ '/images/habitat-banners/banner-grass.jpg' }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-meadow.jpg" data-hover="tooltip"
                         title="{{ __('pages.banner_click') }}">
                        <img class="img-fluid" alt="{{ __('pages.banner_meadow') }}"
                             src="{{ '/images/habitat-banners/banner-meadow.jpg' }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-mtn-flowers.jpg" data-hover="tooltip"
                         title="{{ __('pages.banner_click') }}">
                        <img class="img-fluid" alt="{{ __('pages.banner_mountain') }}"
                             src="{{ '/images/habitat-banners/banner-mtn-flowers.jpg' }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-swamp.jpg" data-hover="tooltip"
                         title="{{ __('pages.banner_click') }}">
                        <img class="img-fluid" alt="{{ __('pages.banner_swamp') }}"
                             src="{{ '/images/habitat-banners/banner-swamp.jpg' }}">
                    </div>
                </div>

                <div class="m-4">
                    <div class="project-banner" data-name="banner-trees.jpg" data-hover="tooltip"
                         title="{{ __('pages.banner_click') }}">
                        <img class="img-fluid" alt="{{ __('pages.banner_trees') }}"
                             src="{{ '/images/habitat-banners/banner-trees.jpg' }}">
                    </div>
                </div>
            </div>

            <div class="modal-footer text-center">
                <button type="button" class="btn btn-outline-primary color-action align-self-center text-uppercase"
                        data-dismiss="modal">{{ __('pages.exit') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->
