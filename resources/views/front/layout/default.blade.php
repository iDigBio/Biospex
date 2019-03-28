<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="{{ _('FSU Department of Biological Science') }}">
    <meta name="csrf-param" content="_token">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="DRVQlYZQo5OkUlUhNG8Re-CgYEB7ELA0I_3qJJlzb0U"/>
    <title>
        {{ _('BIOSPEX') }} | @yield('title')
    </title>
    @include('common.favicon')
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700|Work+Sans:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link href="{{ mix('/css/front.css') }}" rel="stylesheet" type="text/css"/>
    @yield('custom-style')
</head>
<body>
@include('common.notices')
@yield('header')
<div class="container mb-4">
    @yield('content')
    @if(Auth::check())
        @include('common.process-modal')
    @endif
</div>
<!--
sub footer -->
<aside style="background-color: #ededed;">
    <div class="container">
        <!-- Grid row-->
        <div class="row py-3 align-items-center">
            <!-- Grid column -->
            <div class="col-md-10 col-md-offset-1 text-center d-inline d-sm-flex align-items-start justify-content-between">
                <h3>{{ _('Get Connected') }}</h3>
                <!-- Twitter -->
                <a class="figure-img"><i class="fab fa-twitter fa-4x"></i></a>
                <!-- Instagram -->
                <a class="figure-img"><i class="fab fa-instagram fa-4x"></i></a>
                <!--Facebook -->
                <a class="figure-img"><i class="fab fa-facebook fa-4x"></i></a>
                <!--LinkedIn-->
                <a class="figure-img"><i class="fab fa-linkedin fa-4x"></i></a>
            </div>
            <!-- Grid column -->
        </div>
        <!-- Grid row-->
    </div>
</aside>

<!-- Footer -->
<footer class="page-footer font-small blue-grey lighten-5">
    <!-- Footer Links -->
    <div class="container text-center text-md-left">

        <!-- Grid row -->
        <div class="row mt-3 dark-grey-text align-items-center h-100">

            <!-- Grid column -->
            <div class="col-md-3 col-xl-3 mb-4">

                <!-- Content -->
                <img src="/storage/images/biospex_logo.svg" alt="BIOSPEX">
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
                    <a href="{{ route('front.teams.index') }}">{{ _('Team') }}</a>
                </p>
                <p>
                    <a href="{{ route('front.faqs.index') }}">{{ _('FAQ') }}</a>
                </p>
                <p>
                    <a href="{{ route('front.resources.index') }}">{{ _('Resources') }}</a>
                </p>
                <p>
                    <a href="{{ route('front.contact.index') }}">{{ _('Contact') }}</a>
                </p>

            </div>
            <!-- Grid column -->
            <!-- Grid column -->
            <div class="col-md-2  mx-auto mb-4">
                <h6 class="text-uppercase font-weight-bold">{{ __('Content') }}</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">
                <p>
                    <a href="{{ route('front.projects.index') }}">{{ __('Projects') }}</a>
                </p>
                <p>
                    <a href="{{ route('front.expeditions.index') }}">{{ __('Expeditions') }}</a>
                </p>
                <p>
                    <a href="{{ route('front.events.index') }}">{{ __('Events') }}</a>
                </p>
                <p>
                    <a href="{{ route('api.get.index') }}">{{ __('Biospex API') }}</a>
                </p>
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
@if(\Route::current()->getName() !== 'home')
    @include('front.layout.contributors')
@endif
@include('common.php-vars-javascript')
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/front.js') }}"></script>
@yield('custom-script')
</body>
</html>