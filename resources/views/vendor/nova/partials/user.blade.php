<dropdown-trigger class="h-9 flex items-center" slot-scope="{toggle}" :handle-click="toggle">
    @isset($user->email)
        <img
            src="https://secure.gravatar.com/avatar/{{ md5($user->email) }}?size=512"
            class="rounded-full w-8 h-8 mr-3"
        />
    @endisset

    <span class="text-90">
        {{ $user->name ?? $user->email ?? __('Nova User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        <li><a href="{{ route('admin.groups.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.groups') }}</a></li>
        <li><a href="{{ route('admin.projects.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.projects') }}</a></li>
        <li><a href="{{ route('admin.expeditions.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.expeditions') }}</a></li>
        <li><a href="{{ route('admin.events.index') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.events') }}</a></li>
        <li><a href="/admin/nova" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.nova') }}</a></li>
        <li><a href="/admin/telescope" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.telescope') }}</a></li>
        <li><a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">{{ __('pages.logout') }}</a>
        </li>
    </ul>
</dropdown-menu>
