<div class="three col d-block d-sm-block d-md-none" data-toggle="collapse" data-target="#navbarsExampleDefault"
     aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <div class="hamburger" id="hamburger-9">
        <span class="line"></span>
        <span class="line"></span>
        <span class="line"></span>
    </div>
</div>

<div class="collapse navbar-collapse text-capitalize" id="navbarsExampleDefault">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mr-2 dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">ABOUT</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
                <a class="dropdown-item" href="{{ route('teams.get.index') }}">{{ _('TEAM') }}</a>
                <a class="dropdown-item" href="{{ route('faqs.get.index') }}">{{ _('FAQ') }}</a>
            </div>
        </li>
        <li class="nav-item active">
            <a class="nav-link mr-2" href="{{ route('projects.get.index') }}">{{ __('PROJECTS') }} <span
                        class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link" href="{{ route('expeditions.get.index') }}">{{ __('EXPEDITIONS') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link" href="{{ route('expeditions.get.index') }}">{{ __('EVENTS') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link" href="{{ route('contact.get.index') }}">{{ __('CONTACT') }}</a>
        </li>
    </ul>
    @if(Auth::check())
        <div class="btn-group">
            <button type="button" class="btn btn-danger pl-4 pr-4 dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">{{ __('ADMIN') }}
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('admin.groups.index') }}" class="dropdown-item"
                   type="button">{{ _('GROUPS') }}</a>
                <a href="{{ route('admin.projects.index') }}" class="dropdown-item"
                   type="button">{{ _('PROJECTS') }}</a>
                <a href="{{ route('admin.expeditions.index') }}" class="dropdown-item"
                   type="button">{{ _('EXPEDITIONS') }}</a>
                <a href="{{ route('admin.events.index') }}" class="dropdown-item"
                   type="button">{{ _('EVENTS') }}</a>
                <a href="{{ route('app.get.logout') }}" class="dropdown-item"
                   type="button">{{ _('LOG OUT') }}</a>
            </div>
        </div>
    @else
        <a href="{{ route('app.get.login') }}" type="button" class="btn btn-danger pl-4 pr-4">{{ __('LOGIN') }}</a>
    @endif
</div>