<li><a href="{{ route('home.get.vision') }}">{{ trans('pages.vision_menu') }}</a></li>
<li><a href="{{ route('web.faqs.index') }}">{{ trans('pages.faq') }}</a></li>
<li><a href="{{ route('web.resources.index') }}">{{ trans('pages.resources') }}</a></li>
<li><a href="{{ route('home.get.contact') }}">{{ trans('pages.contact') }}</a></li>
<li><a href="{{ route('web.teams.index') }}">{{ trans('pages.team_menu') }}</a></li>
<!-- Navbar Right Menu -->
@can('admin', $authUser)
    <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('pages.admin') }}</a></li>
@endcan