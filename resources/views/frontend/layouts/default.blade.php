<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('frontend.layouts.partials.htmlheader')
@show

<body class="{{ Route::currentRouteName() }}">
@include('frontend.layouts.navigation')

@if (Route::currentRouteName() == 'home')
    @yield('homepage')
@elseif(Route::currentRouteName() == 'home.get.project')
    @yield('project')
@else
    <div class="container-fluid">
        @include('frontend.layouts.notices')
        @yield('content')
    </div>
@endif
@if (Auth::check())
    @include('frontend.layouts.partials.process-modal')
@endif
@include('frontend.layouts.footer')
<!-- ./ footer -->

@section('scripts')
    @include('frontend.layouts.partials.scripts')
@show

</body>
</html>
