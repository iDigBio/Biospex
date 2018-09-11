<!-- Navbar -->
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="navbar-header pull-left">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        &nbsp;
        <div class="btn-group">
            <a class="navbar-brand" href="{{ route('home') }}"><img src="/images/biospex-header-logo.jpg"
                                                                    alt="{{ trans('pages.sitename') }}"/></a>
        </div>
    </div>
    <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav pull-left">
            @if(Auth::check())
                @include('frontend.layouts.partials.authmenu')
            @endif
            @include('frontend.layouts.partials.commonmenu')
        </ul>
        <ul class="nav navbar-nav  pull-right">
            <li class="translate"><div id="google_translate_element"></div><script type="text/javascript">
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
                    }
                </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
            </li>
            @if (Auth::check())
                @include('frontend.layouts.partials.authuser')
            @else
                @include('frontend.layouts.partials.noauthmenu')
            @endif
        </ul>
    </div>
    <!--/.nav-collapse -->
</nav>