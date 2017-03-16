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
            <a class="navbar-brand" href="{{ route('home') }}"><img src="/img/biospex-header-logo.jpg"
                                                                    alt="{{ trans('pages.sitename') }}"/></a>
        </div>
    </div>
    <div class="collapse navbar-collapse">
        @if (config('config.translate'))
            @include('frontend.layouts.partials.translate')
        @endif
        <ul class="nav navbar-nav pull-left">

            @include('frontend.layouts.partials.commonmenu')
        </ul>
        <ul class="nav navbar-nav  pull-right">

                @include('frontend.layouts.partials.noauthmenu')

        </ul>
    </div>
    <!--/.nav-collapse -->
</nav>