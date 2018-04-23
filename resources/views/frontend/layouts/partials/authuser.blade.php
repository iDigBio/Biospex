<!-- User Account Menu -->
<li class="dropdown user user-menu">
    <!-- Menu Toggle Button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <!-- The user image in the navbar-->
        <img src="{{ $authUser->profile->avatar_small }}" class="user-image" alt="User Image"/>
        <!-- hidden-xs hides the username on small devices so only the image appears. -->
        <span class="hidden-xs">{{ $authUser->email }}</span>
    </a>
    <ul class="dropdown-menu">
        <!-- Menu Body -->
        <li class="user-body">
            <div class="pull-left">
                <a href="/users/{{ $authUser->id }}/edit" class="btn btn-primary btn-flat">Profile</a>
            </div>
            <div class="pull-right">
                <a href="{{ route('app.get.logout') }}"
                   class="btn btn-danger btn-flat">{{ trans('pages.logout') }}</a>
            </div>
        </li>
    </ul>
</li>
<!-- /.navbar-custom-menu -->