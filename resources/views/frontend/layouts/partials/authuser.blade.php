<!-- Notifications Menu -->
<li class="dropdown notifications-menu">
    <!-- Menu toggle button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        {!! null === $notifications ? '' :'<span class="label label-warning">' . count($notifications) . '</span>' !!}
    </a>
    <ul class="dropdown-menu">
        <li class="header">{{ trans_choice('pages.count_notifications', count($notifications), ['count' => count($notifications)]) }}</li>
        <li>
            <!-- Inner Menu: contains the notifications -->
            <ul class="menu">
                @each('frontend.layouts.partials.notifications', $notifications, 'notification', 'frontend.layouts.partials.no-notifications')
            </ul>
        </li>
        <li class="footer"><a href="{{ route('web.notifications.index') }}">{{ trans('pages.view_all') }}</a></li>
    </ul>
</li>
<!-- User Account Menu -->
<li class="dropdown user user-menu">
    <!-- Menu Toggle Button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <!-- The user image in the navbar-->
        <img src="{{ Auth::getUser()->profile->avatar->url('small') }}" class="user-image" alt="User Image"/>
        <!-- hidden-xs hides the username on small devices so only the image appears. -->
        <span class="hidden-xs">{{ Auth::getUser()->email }}</span>
    </a>
    <ul class="dropdown-menu">
        <!-- Menu Body -->
        <li class="user-body">
            <div class="pull-left">
                <a href="/users/{{ Auth::getUser()->id }}/edit" class="btn btn-primary btn-flat">Profile</a>
            </div>
            <div class="pull-left">
                <a href="{{ route('auth.get.logout') }}"
                   class="btn btn-danger btn-flat">{{ trans('pages.logout') }}</a>
            </div>
        </li>
    </ul>
</li>
<!-- /.navbar-custom-menu -->