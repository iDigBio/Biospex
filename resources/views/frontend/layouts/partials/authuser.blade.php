<!-- User Account Menu -->
<li class="dropdown user user-menu">
    <!-- Menu Toggle Button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <!-- The user image in the navbar-->
        <img src="{{ $authUser->profile->present()->avatar_small }}" class="user-image" alt="User Image"/>
        <!-- hidden-xs hides the username on small devices so only the image appears. -->
        <span class="hidden-xs">{{ $authUser->email }}</span>
    </a>
    <ul class="dropdown-menu">
        <!-- Menu Body -->
        <li>
            <a href="/users/{{ $authUser->id }}/edit">Profile</a>
        </li>
        <li>
            <a href="{{ route('app.get.logout') }}">{{ trans('pages.logout') }}</a>
        </li>
        @can('admin', Auth::user())
            <li><a href="/nova">Admin</a></li>
        @endcan
    </ul>
</li>
<!-- /.navbar-custom-menu -->