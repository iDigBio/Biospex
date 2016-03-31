<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>
        @section('title')
        @show
    </title>
    @section ('styles')
    @show
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/biospex.css" rel="stylesheet">
</head>
<body class="{{ Route::currentRouteName() }}">
@include('frontend.layouts.navigation')

@if (Route::currentRouteName() == 'home')
    @yield('homepage')
@elseif(Route::currentRouteName() == 'home.get.project')
    @yield('project')
@else
    <div class="container-fluid">
        @include('frontend.layouts.notifications')
        @yield('content')
    </div>
    @endif

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
