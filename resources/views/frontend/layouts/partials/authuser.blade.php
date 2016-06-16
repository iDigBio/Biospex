<li {{ (Request::is('users/' . Auth::getUser()->id . '/edit') ? 'class=active' : '') }}>
    <a href="/users/{{ Auth::getUser()->id }}/edit">{{ Auth::getUser()->email }}</a>
</li>
<li><a href="{{ route('auth.get.logout') }}">{{ trans('pages.logout') }}</a></li>
@can('admin', Auth::getUser())
    <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('pages.admin') }}</a></li>
@endcan