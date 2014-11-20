<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="_token" content="{{ csrf_token() }}" />
    <title>
        @section('title')
        @show
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @section ('styles')
    @show
    <?= stylesheet_link_tag() ?>
    <?= javascript_include_tag() ?>
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body class="{{ Route::currentRouteName() }}">

<!-- Navbar -->
<div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ URL::route('home') }}"><img src="/assets/biospex.png" alt="{{trans('pages.sitename')}}" /></a>
        </div>
        <div class="collapse navbar-collapse">
            @if (Config::get('config.translate'))
            <ul class="nav navbar-nav">
                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">{{ trans('pages.translate') }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        @foreach (Config::get('supportedLocales') as $key => $lang)
                        <li><a href="{{ Local::getLocalizedURL($key) }}">{{ trans('pages.translate-' . $key) }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            @endif
            {{ $topmenu }}
            <ul class="nav navbar-nav navbar-right">
                @if (Sentry::check())
                <li {{ (Request::is('users/' . Session::get('userId') . '/edit') ? 'class="active"' : '') }}>
                    <a href="/users/{{ Session::get('userId') }}/edit">{{ Session::get('email') }}</a>
                </li>
                <li><a href="{{ URL::route('logout') }}">{{ trans('pages.logout') }}</a></li>
                @else
                <li
                {{ (Request::is('login') ? 'class="active"' : '') }}><a href="{{ URL::route('login') }}">{{trans('pages.login')}}</a></li>
                <li
                {{ (Request::is('users/create') ? 'class="active"' : '') }}><a href="{{ URL::route('register') }}">{{trans('pages.register')}}</a></li>
                @endif
                <li><a href="{{ URL::route('help') }}">{{ trans('pages.help') }}</a></li>
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</div>
<!-- ./ navbar -->
@if (Route::currentRouteName() == 'home')
    @yield('homepage')
@else
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
@endif

<!-- Footer -->
@include('layouts/footer')
<!-- ./ footer -->
</body>
</html>
