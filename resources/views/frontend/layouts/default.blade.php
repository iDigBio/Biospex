<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{ csrf_token() }}" />
    <title>
        @section('title')
        @show
    </title>
    @section ('styles')
    @show
    <link href="/css/biospex.css" rel="stylesheet">
    <link href="/css/bootstrap.css" rel="stylesheet">
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
            <a class="navbar-brand" href="{{ route('home') }}"><img src="/img/biospex.png" alt="{{ trans('pages.sitename') }}" /></a>
        </div>
        <div class="collapse navbar-collapse">
            @if (config('config.translate'))
            <ul class="nav navbar-nav">
                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">{{ trans('pages.translate') }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        @foreach (config('supportedLocales') as $key => $lang)
                        <li><a href="{{ Local::getLocalizedURL($key) }}">{{ trans('pages.translate-' . $key) }}</a></li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            @endif
            @if(Auth::check())
            <ul class="nav navbar-nav">
                <li {{ preg_match('/groups/', Route::currentRouteName()) ? 'class="active"' : "" }}><a href="{{ route('groups.get.index') }}">Groups</a></li>
                <li {{ preg_match('/projects/', Route::currentRouteName()) ? 'class="active"' : "" }}><a href="{{ route('projects.get.index') }}">Projects</a></li>
                <li {{ preg_match('/expeditions/', Route::currentRouteName()) ? 'class="active"' : "" }}><a href="{{ route('expeditions.get.index') }}">Expeditions</a></li>
            </ul>
            @endif
            <ul class="nav navbar-nav navbar-right">
                @if (Auth::check())
                <li {{ (Request::is('users/' . Auth::getUser()->id . '/edit') ? 'class=active' : '') }}>
                    <a href="/users/{{ Auth::getUser()->id }}/edit">{{ Auth::getUser()->email }}</a>
                </li>
                <li><a href="{{ route('auth.get.logout') }}">{{ trans('pages.logout') }}</a></li>
                @else
                <li
                {{ Request::is('login') ? 'class=active' : '' }}><a href="{{ route('auth.get.login') }}">{{trans('pages.login')}}</a></li>
                <li
                {{ Request::is('users/create') ? 'class=active' : '' }}><a href="{{ route('auth.get.register') }}">{{trans('pages.register')}}</a></li>
                @endif
                <li><a href="{{ route('home.get.help') }}">{{ trans('pages.help') }}</a></li>
                <li><a href="{{ route('home.get.contact') }}">{{ trans('pages.contact') }}</a></li>
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
        @include('frontend.layouts.notifications')
        <!-- ./ notifications -->

        <!-- Content -->
        @yield('content')
        <!-- ./ content -->
    </div>
    <!-- ./ container -->
@endif

<!-- Footer -->
@include('frontend.layouts.footer')
<!-- ./ footer -->
<script src="/js/biospex.js"></script>
<script src="/js/bootstrap.js"></script>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</body>
</html>
