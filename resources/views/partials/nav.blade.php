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
        @if(Auth::check())
            <li class="nav-item nav-btn dropdown">
                <a class="nav-link dropdown-toggle text-uppercase" href="#" id="dropdown02" data-toggle="dropdown" aria-haspopup="true"
                   aria-expanded="false">{{ t('Admin') }}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown02">
                    <a href="{{ route('admin.get.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Dashboard')  }}</a>
                    <a href="{{ route('admin.ingest.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Ingest')  }}</a>
                    <a href="{{ route('admin.export.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Export')  }}</a>
                    <a href="{{ route('admin.version.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Version')  }}</a>
                    <a href="{{ route('admin.product.index') }}" class="dropdown-item text-uppercase"
                       type="button">{{ t('Product')  }}</a>
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