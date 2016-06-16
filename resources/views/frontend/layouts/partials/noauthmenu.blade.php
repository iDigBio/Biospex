<li {{ Request::is('login') ? 'class=active' : '' }}><a
            href="{{ route('auth.get.login') }}">{{trans('pages.login')}}</a></li>
<li {{ Request::is('users/create') ? 'class=active' : '' }}><a
            href="{{ route('auth.get.register') }}">{{trans('pages.register')}}</a></li>