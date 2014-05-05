<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>
        @section('title')
        @show
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap: compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    @section ('styles')
    @show
    {{ HTML::style('css/style.css') }}
</head>

<body>


<!-- Navbar -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ URL::route('home') }}">{{trans('pages.sitename')}}</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">{{ trans('pages.translate') }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        @foreach (Config::get('supportedLocales') as $key => $lang)
                        <li><a href="{{ Local::getLocalizedURL($key) }}">{{ trans('pages.translate-' . $key) }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            {{ $topmenu }}
            <ul class="nav navbar-nav navbar-right">
                @if (Sentry::check())
                <li {{ (Request::is('users/show/' . Session::get('userId')) ? 'class="active"' : '') }}>
                    <a href="/users/{{ Session::get('userId') }}">{{ Session::get('email') }}</a>
                </li>
                <li><a href="{{ URL::route('logout') }}">{{ trans('pages.logout') }}</a></li>
                @else
                <li
                {{ (Request::is('login') ? 'class="active"' : '') }}><a href="{{ URL::route('login') }}">{{trans('pages.login')}}</a></li>
                <li
                {{ (Request::is('users/create') ? 'class="active"' : '') }}><a href="{{ URL::route('register') }}">{{trans('pages.register')}}</a></li>
                @endif
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</div>
<!-- ./ navbar -->

<!-- Container -->
<div class="container">
    <!-- Notifications -->
    @include('layouts/notifications')
    <!-- ./ notifications -->

    <!-- Content -->
    @yield('content')
    <!-- ./ content -->
</div>

<!-- ./ container -->

<!-- Javascripts
================================================== -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/additional-methods.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
@section('scripts')
@show
<!-- Thanks to Zizaco for the Restfulizer script.  http://zizaco.net  -->
{{ HTML::script('js/restfulizer.js') }}
{{ HTML::script('js/script.js') }}
</body>
</html>
