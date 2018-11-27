<div class="three col d-block d-sm-block d-md-none" data-toggle="collapse" data-target="#navbarsExampleDefault"
     aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <div class="hamburger" id="hamburger-9">
        <span class="line"></span>
        <span class="line"></span>
        <span class="line"></span>
    </div>
</div>

<div class="collapse navbar-collapse">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mr-2 dropdown">
            <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">{{ __('about') }}</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
                <a class="dropdown-item text-uppercase" href="{{ route('teams.get.index') }}">{{ _('team') }}</a>
                <a class="dropdown-item text-uppercase" href="{{ route('faqs.get.index') }}">{{ _('faq') }}</a>
            </div>
        </li>
        <li class="nav-item active">
            <a class="nav-link mr-2 text-uppercase" href="{{ route('projects.get.index') }}">{{ __('projects') }} <span
                        class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('expeditions.get.index') }}">{{ __('expeditions') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('events.get.index') }}">{{ __('events') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('contact.get.index') }}">{{ __('contact') }}</a>
        </li>
        @if(Auth::check())
            <li class="nav-item nav-btn mr-2 dropdown">
                <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown02" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">{{ __('admin') }}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown02">
                    <a href="{{ route('admin.groups.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('groups') }}</a>
                    <a href="{{ route('admin.projects.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('projects') }}</a>
                    <a href="{{ route('admin.expeditions.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('expeditions') }}</a>
                    <a href="{{ route('admin.events.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('events') }}</a>
                    <a href="{{ route('app.get.logout') }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('logout') }}</a>
                    @can('isAdmin', Auth::user())
                        <a href="/nova" class="dropdown-item text-uppercase"
                           type="button">{{ _('nova') }}</a>
                    @endcan
                </div>
            </li>
        @else
            <li class="nav-item nav-btn mr-2">
                <a class="nav-link text-uppercase" href="{{ route('app.get.login') }}">{{ __('login') }}</a>
            </li>
        @endif
    </ul>
</div>