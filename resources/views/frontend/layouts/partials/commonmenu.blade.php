<li><a href="{{ route('home.get.vision') }}">{{ trans('pages.vision_menu') }}</a></li>
<li><a href="{{ route('frontend.faqs.index') }}">{{ trans('pages.faq') }}</a></li>
<li><a href="{{ route('frontend.resources.index') }}">{{ trans('pages.resources') }}</a></li>
<li><a href="{{ route('home.get.contact') }}">{{ trans('pages.contact') }}</a></li>
<li><a href="{{ route('frontend.teams.index') }}">{{ trans('pages.team') }}</a></li>
<!-- Navbar Right Menu -->
@can('admin', Auth::user())
    <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('pages.admin') }}</a></li>
@endcan