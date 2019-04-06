<div class="three col d-block d-sm-block d-md-none" data-toggle="collapse" data-target="#navbarsExampleDefault"
     aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <div class="hamburger" id="hamburger-9">
        <span class="line"></span>
        <span class="line"></span>
        <span class="line"></span>
    </div>
</div>

<div class="collapse navbar-collapse" id="navbarsExampleDefault">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mr-2 dropdown">
            <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">{{ __('about') }}</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
                <a class="dropdown-item text-uppercase" href="{{ route('front.teams.index') }}">{{ _('team') }}</a>
                <a class="dropdown-item text-uppercase" href="{{ route('front.faqs.index') }}">{{ _('faq') }}</a>
                <a class="dropdown-item text-uppercase" href="{{ route('front.resources.index') }}">{{ _('resources') }}</a>
            </div>
        </li>
        <li class="nav-item active">
            <a class="nav-link mr-2 text-uppercase" href="{{ route('front.projects.index') }}">{{ __('projects') }} <span
                        class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.expeditions.index') }}">{{ __('expeditions') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.events.index') }}">{{ __('events') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.contact.index') }}">{{ __('contact') }}</a>
        </li>
        @if(Auth::check())
            <li class="nav-item nav-btn dropdown">
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
                    <a href="#" class="preventDefault dropdown-item text-uppercase"
                       type="button"
                       data-toggle="modal"
                       data-target="#process-modal">{{ __('Processes') }}</a>
                    @can('isAdmin', Auth::user())
                        <a href="/admin/nova" class="dropdown-item text-uppercase"
                           type="button">{{ _('nova') }}</a>
                        <a href="/admin/telescope" class="dropdown-item text-uppercase"
                           type="button">{{ _('telescope') }}</a>
                    @endcan
                    <a href="{{ route('admin.users.edit', [Auth::id()]) }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('account') }}</a>
                    <a href="{{ route('app.get.logout') }}" class="dropdown-item text-uppercase"
                       type="button">{{ _('logout') }}</a>
                </div>
            </li>
        @else
            <li class="nav-item nav-btn">
                <a class="nav-link text-uppercase mx-auto" href="{{ route('app.get.login') }}">{{ __('login') }}</a>
            </li>
        @endif
    </ul>
</div>