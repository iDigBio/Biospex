<li class="{{ (Route::currentRouteName() === 'web.groups.index') ? 'active' : '' }}"><a
            href="{{ route('web.groups.index') }}">Groups</a></li>
<li class="{{ (Route::currentRouteName() === 'web.projects.index') ? 'active' : '' }}"><a
            href="{{ route('web.projects.index') }}">Projects</a></li>
<li class="{{ (Route::currentRouteName() === 'web.expeditions.index') ? 'active' : '' }}"><a
            href="{{ route('web.expeditions.index') }}">Expeditions</a></li>
<li class=""><a class="noClick" href="#" data-toggle="modal" data-target="#processModal">Processes</a>
</li>