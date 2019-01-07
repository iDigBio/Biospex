<!--
sub footer -->
<aside style="background-color: #ededed;">
    <div class="container">
        <!-- Grid row-->
        <div class="row py-3 align-items-center">
            <!-- Grid column -->
            <div class="col-md-10 col-md-offset-1 text-center d-inline d-sm-flex align-items-start justify-content-between">
                <h3>{{ _('Get Connected') }}</h3>
                <!-- Facebook -->
                <a class="figure-img"><i class="fab fa-twitter fa-4x"></i></a>
                <!-- Twitter -->
                <a class="figure-img"><i class="fab fa-instagram fa-4x"></i></a>
                <!--Linkedin -->
                <a class="figure-img"><i class="fab fa-facebook fa-4x"></i></a>
                <!--Instagram-->
                <a class="figure-img"><i class="fas fa-envelope fa-4x"></i></a>
            </div>
            <!-- Grid column -->
        </div>
        <!-- Grid row-->
    </div>
</aside>

<!-- Footer -->
<footer class="page-footer font-small blue-grey lighten-5">
    <!-- Footer Links -->
    <div class="container text-center text-md-left mt-5">

        <!-- Grid row -->
        <div class="row mt-3 dark-grey-text">

            <!-- Grid column -->
            <div class="col-md-3 col-xl-3 mb-4">

                <!-- Content -->
                <h6><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"></h6>
                <p class="small text-justify pt-2">{{ __('is funded by a grant from the National Science Foundation\'s Advances in Biological Informatics Program (Award Number 1458550). iDigBio is funded by a grant from the National Science Foundation\'s Advancing Digitization of Biodiversity Collections Program (Cooperative Agreement EF-1115210). Any opinions, findings, and conclusions or recommendations expressed in this material are those of the author(s) and do not necessarily reflect the views of the National Science Foundation.') }}</p>
            </div>
            <!-- Grid column -->
            <div class="col-sm-1">
            </div>
            <!-- Grid column -->
            <div class="col-md-2 col-12 mx-auto mb-4">
                <!-- Links -->
                <h6 class="text-uppercase font-weight-bold">{{ __('About') }}</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">
                <p>
                    <a href="{{ route('teams.get.index') }}">{{ _('Team') }}</a>
                </p>
                <p>
                    <a href="{{ route('faqs.get.index') }}">{{ _('FAQ') }}</a>
                </p>
                <p>
                    <a href="{{ route('contact.get.index') }}">{{ _('Contact') }}</a>
                </p>

            </div>
            <!-- Grid column -->
            <!-- Grid column -->
            <div class="col-md-2  mx-auto mb-4">
                <h6 class="text-uppercase font-weight-bold">{{ __('Resources') }}</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">
                <p>
                    <a href="{{ route('projects.get.index') }}">{{ __('Projects') }}</a>
                </p>
                <p>
                    <a href="{{ route('expeditions.get.index') }}">{{ __('Expeditions') }}</a>
                </p>
                <p>
                    <a href="{{ route('events.get.index') }}">{{ __('Events') }}</a>
                </p>
                <p>
                    <a href="{{ config('config.api_url') }}">{{ __('Biospex API') }}</a>
                </p>
            </div>
            <!-- Grid column -->
            <!-- Grid column -->
            <div class="col-md-2 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">

                <!-- Links -->
                <h6 class="text-uppercase font-weight-bold">{{ __('Contact') }}</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">

                <p>{{ __('Tallahassee, FL 32301, USA') }}</p>
                <p>info@fsu.com</p>
                <p>+ 01 850 567 88</p>
                <p>+ 01 850 567 89</p>

            </div>
            <!-- Grid column -->

        </div>
        <!-- Grid row -->

    </div>
    <!-- Footer Links -->

    <!-- Copyright -->
    <div class="text-center py-3" style="color: #e1e1e1;">{{ __('© 2019 Copyright') }}
        <a href="https://www.bio.fsu.edu/"> {{ __('FSU Deptartment of Biological Science') }}</a>
    </div>
    <!-- Copyright -->

</footer>