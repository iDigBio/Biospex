<!-- Navbar -->
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        &nbsp;
        <div class="btn-group">
            <a class="navbar-brand" href="{{ route('home') }}"><img src="/img/biospex.png"
                                                                    alt="{{ trans('pages.sitename') }}"/></a>
        </div>
    </div>
    <div class="collapse navbar-collapse">
        @if (config('config.translate'))
            <ul class="nav navbar-nav">
                <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown"
                                        href="#">{{ trans('pages.translate') }} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        @foreach (config('supportedLocales') as $key => $lang)
                            <li>
                                <a href="{{ Local::getLocalizedURL($key) }}">{{ trans('pages.translate-' . $key) }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        @endif
        @if(Auth::check())
            {{ Route::currentRouteName()  }}
            <ul class="nav navbar-nav">
                <li class="{{ (Route::currentRouteName() == 'groups.get.index') ? 'active' : '' }}"><a
                            href="{{ route('groups.get.index') }}">Groups</a></li>
                <li class="{{ (Route::currentRouteName() == 'projects.get.index') ? 'active' : '' }}"><a
                            href="{{ route('projects.get.index') }}">Projects</a></li>
                <li class="{{ (Route::currentRouteName() == 'expeditions.get.index') ? 'active' : '' }}"><a
                            href="{{ route('expeditions.get.index') }}">Expeditions</a></li>
                <li class=""><a class="noClick" href="#" data-toggle="modal" data-target="#processModal">Processes</a>
                </li>
            </ul>
        @endif
        <ul class="nav navbar-nav pull-right">
            @if (Auth::check())
                <li {{ (Request::is('users/' . Auth::getUser()->id . '/edit') ? 'class=active' : '') }}>
                    <a href="/users/{{ Auth::getUser()->id }}/edit">{{ Auth::getUser()->email }}</a>
                </li>
                <li><a href="{{ route('auth.get.logout') }}">{{ trans('pages.logout') }}</a></li>
            @else
                <li
                        {{ Request::is('login') ? 'class=active' : '' }}><a
                            href="{{ route('auth.get.login') }}">{{trans('pages.login')}}</a></li>
                <li
                        {{ Request::is('users/create') ? 'class=active' : '' }}><a
                            href="{{ route('auth.get.register') }}">{{trans('pages.register')}}</a></li>
            @endif
            <li><a href="{{ route('home.get.help') }}">{{ trans('pages.help') }}</a></li>
            <li><a href="{{ route('home.get.contact') }}">{{ trans('pages.contact') }}</a></li>
                <li><a href="{{ route('home.get.team') }}">{{ trans('pages.team_menu') }}</a></li>
        </ul>
    </div>
    <!--/.nav-collapse -->
</nav>