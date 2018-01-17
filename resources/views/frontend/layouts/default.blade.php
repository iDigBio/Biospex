<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('frontend.layouts.partials.htmlheader')
@show

<body class="{{ Route::currentRouteName() }}">
@include('frontend.layouts.navigation')
<div id="google_translate_element"></div><script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.FloatPosition.TOP_LEFT}, 'google_translate_element');
    }
</script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

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
