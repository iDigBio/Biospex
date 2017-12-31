<li {{ Request::is('login') ? 'class=active' : '' }}><a
            href="{{ route('app.get.login') }}">{{trans('pages.login')}}</a></li>
<li {{ Request::is('users/create') ? 'class=active' : '' }}><a
            href="{{ route('app.get.register') }}">{{trans('pages.register')}}</a></li>