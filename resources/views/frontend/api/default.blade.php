<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-param" content="_token">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="DRVQlYZQo5OkUlUhNG8Re-CgYEB7ELA0I_3qJJlzb0U"/>
    <title>
        @section('title')
        @show
    </title>
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- Bootstrap built locally with Biospex -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" type="text/css"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    @yield('custom-style')
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 text-center col-md-offset-2">
            <img src="{{ asset('images/logo.png') }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="text-center">
                <a class="btn btn-default" href="{{ route('home') }}">Biospex</a>
                <a class="btn btn-default" href="https://biospex.docs.apiary.io/#">API Docs</a>
                <a class="btn btn-default" href="https://github.com/iDigBio/Biospex">GitHub</a>
                @apiuser
                <a class="btn btn-danger" rel="nofollow" data-method="delete" href="{{ route('api.get.logout') }}">Sign
                    Out</a>
                @else
                    <a class="btn btn-success" href="{{ route('api.get.login') }}">Sign In</a>
                    <a class="btn btn-primary" rel="nofollow" data-method="delete"
                       href="{{ route('api.get.register') }}">Register</a>
                    @endapiuser
            </div>
        </div>
    </div>
    @yield('content')
</div>
<div class="container" id="footer-home">
    <!-- Footer -->
    <ul id="social-list">
        <li><a href="https://www.facebook.com/biospex" target="_blank">
                <img alt="Like iDigBio on Facebook" src="{{ asset('images/facebook.png') }}"/></a></li>
        <li><a href="https://twitter.com/biospex" target="_blank">
                <img alt="Follow iDigbio on Twitter" src="{{ asset('images/twitter.png') }}"/></a></li>
    </ul>
    <ul id="logo-list">
        <li><a href="http://idigbio.org">
                <img alt="iDigBio logo" class="logo-center" src="{{ asset('images/idigbio.png') }}"
                     style="height: 60px; "/></a></li>
        <li><a href="http://ufl.edu">
                <img alt="University of Florida logo" class="logo-center" src="{{ asset('images/uf.png') }}"
                     style="width: 60px; height: 60px; "/></a></li>
        <li><a href="http://fsu.edu">
                <img alt="Florida State University logo" class="logo-center" src="{{ asset('images/fsu.png') }}"
                     style="width: 60px; height: 60px; "/></a>
        </li>
        <li><a href="http://flmnh.ufl.edu">
                <img alt="Florida Museum logo" class="logo-center" src="{{ asset('images/flmnh.png') }}"
                     style="width: 60px; height: 60px; "/></a></li>
        <li><a href="http://nsf.gov">
                <img alt="National Science Foundation logo" class="logo-center" src="{{ asset('images/nsf.png') }}"
                     style="width: 60px; height: 60px; "/></a>
        </li>
    </ul>
    <p class="small">{!! trans('html.footer-text') !!}</p>
    <p class="text-center">{{ link_to_route('api.get.index', 'Biospex API') }}</p>
    <!-- ./ footer -->
</div>
<!-- ./ footer -->
<!-- REQUIRED JS SCRIPTS -->
@include('frontend.layouts.partials.php-vars-javascript')
<script src="{{ mix('/js/app.js') }}"></script>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
@yield('custom-script')
</body>
</html>