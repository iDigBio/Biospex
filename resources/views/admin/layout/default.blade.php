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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:700|Work+Sans">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link href="{{ mix('/css/admin.css', '/admin') }}" rel="stylesheet" type="text/css"/>
    @yield('custom-style')
</head>
<body>
<header>
    <nav class="header-admin navbar navbar-expand-md box-shadow">
        <a href="/"><img src="/storage/images/biospex_logo.svg" alt="BIOSPEX"
                         class="my-0 mr-md-auto top-logo-admin font-weight-normal"/></a>
        @include('common.nav')
    </nav>
</header>
<div class="container mb-4">
    @include('common.notices')
    @yield('content')
    @include('common.process-modal')
</div>
<!-- Footer -->
<footer class="page-footer font-small blue-grey lighten-5">
    <!-- Copyright -->
    <div class="footer-copyright text-center text-black-50 py-3">{{ __('© 2019 Copyright') }}
        <a class="dark-grey-text" href="#"> {{ __('FSU Deptartment of Biological Science') }}</a>
    </div>
    <!-- Copyright -->
</footer>
@include('common.php-vars-javascript')
<script src="{{ mix('/js/manifest.js', 'admin') }}"></script>
<script src="{{ mix('/js/vendor.js', 'admin') }}"></script>
<script src="{{ mix('/js/admin.js', 'admin') }}"></script>
@yield('custom-script')
</body>
</html>