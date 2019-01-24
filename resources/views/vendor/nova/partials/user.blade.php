<dropdown-trigger class="h-9 flex items-center" slot-scope="{toggle}" :handle-click="toggle">
    <img src="https://secure.gravatar.com/avatar/{{ md5(auth()->user()->email) }}?size=512" class="rounded-full w-8 h-8 mr-3"/>

    <span class="text-90">
        {{ auth()->user()->name }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        <li><a href="{{ route('admin.groups.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ _('Groups') }}</a></li>
        <li><a href="{{ route('admin.projects.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ _('Projects') }}</a></li>
        <li><a href="{{ route('admin.expeditions.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ _('Expeditions') }}</a></li>
        <li><a href="{{ route('admin.events.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ _('Events') }}</a></li>
        <li><a href="/admin/nova" class="block no-underline text-90 hover:bg-30 p-3">{{ _('Nova') }}</a></li>
        <li><a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ __('Logout') }}</a>
        </li>
    </ul>
</dropdown-menu>
