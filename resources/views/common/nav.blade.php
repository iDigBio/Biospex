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
            <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown00" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">{{ t('About') }}</a>
            <div class="dropdown-menu" aria-labelledby="dropdown00">
                <a class="dropdown-item text-uppercase" href="{{ route('front.teams.index') }}">{{ t('Team') }}</a>
                <a class="dropdown-item text-uppercase" href="{{ route('front.faqs.index') }}">{{ t('FAQ') }}</a>
                <a class="dropdown-item text-uppercase" href="{{ route('front.resources.index') }}">{{ t('Resources') }}</a>
            </div>
        </li>
        <li class="nav-item active">
            <a class="nav-link mr-2 text-uppercase" href="{{ route('front.projects.index') }}">{{ t('Projects') }} <span
                        class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.expeditions.index') }}">{{ t('Expeditions') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.events.index') }}">{{ t('Events') }}</a>
        </li>
        @if(Auth::check())
        <li class="nav-item mr-2 dropdown">
            <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false">{{ t('WeDigBio') }}</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
                <a class="dropdown-item text-uppercase" href="#"
                   data-toggle="modal"
                   data-target="#wedigbio-progress-modal"
                   data-href="{{ route('ajax.get.wedigbio-progress', ['dateId' => 0]) }}"
                   data-channel="{{ config('config.poll_wedigbio_progress_channel') . '.' . 0 }}"
                   data-date="0">{{ t('Progress') }}</a>
                <a class="dropdown-item text-uppercase" href="#"
                   data-toggle="modal"
                   data-target="#wedigbio-rate-modal"
                   data-date="0"
                   data-href="{{ route('ajax.get.wedigbio-rate', ['dateId' => 0]) }}">{{ t('Rates') }}</a>
                <a class="dropdown-item text-uppercase" href="{{ route('front.wedigbio.index') }}">{{ t('Past Results') }}</a>
                <a class="dropdown-item text-uppercase" href="https://wedigbio.org" target="_blank">{{ t('WeDigBio Website') }}</a>
            </div>
        </li>
        @endif
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.bingos.index') }}">{{ t('Games') }}</a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link text-uppercase" href="{{ route('front.contact.index') }}">{{ t('Contact') }}</a>
        </li>
        @if(Auth::check())
            <li class="nav-item nav-btn dropdown">
                <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown02" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">{{ t('Admin') }}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown02">
                    <a href="{{ route('admin.groups.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Groups') }}</a>
                    <a href="{{ route('admin.projects.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Projects') }}</a>
                    <a href="{{ route('admin.expeditions.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Expeditions') }}</a>
                    <a href="{{ route('admin.events.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Events') }}</a>
                    <a href="{{ route('admin.bingos.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Games') }}</a>
                    <a href="#" class="preventDefault dropdown-item text-uppercase"
                       type="button"
                       data-toggle="modal"
                       data-target="#process-modal">{{ t('Processes') }}</a>
                    @can('isAdmin', Auth::user())
                        <a href="/admin/nova" target="_blank" class="dropdown-item text-uppercase"
                           type="button">{{ t('Nova') }}</a>
                        <a href="{{ route('admin.mail.index') }}" class="dropdown-item text-uppercase"
                           type="button">{{ t('Mail') }}</a>
                    @endcan
                    <a href="{{ route('admin.update.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Updates') }}</a>
                    <a href="{{ route('admin.users.edit', [Auth::id()]) }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Account') }}</a>
                    <a href="{{ route('app.get.logout') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Logout') }}</a>
                </div>
            </li>
        @else
            <li class="nav-item nav-btn">
                <a class="nav-link text-uppercase mx-auto" href="{{ route('app.get.login') }}">{{ t('Login') }}</a>
            </li>
        @endif
    </ul>
</div>