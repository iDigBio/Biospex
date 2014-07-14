@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.account_profile')
@stop

{{-- Content --}}
@section('content')
	<h4>@lang('pages.account_profile')</h4>

  	<div class="well clearfix">
	    <div class="col-md-8">
		    @if ($user->first_name)
		    	<p><strong>@lang('pages.first_name'):</strong> {{ $user->first_name }} </p>
			@endif
			@if ($user->last_name)
		    	<p><strong>@lang('pages.last_name'):</strong> {{ $user->last_name }} </p>
			@endif
		    <p><strong>@lang('pages.email'):</strong> {{ $user->email }}</p>
		    
		</div>
		<div class="col-md-4">
			<p><em>@lang('pages.account_created'): {{ $user->created_at }}</em></p>
			<p><em>@lang('pages.last_updated'): {{ $user->updated_at }}</em></p>
			<button class="btn btn-primary" onClick="location.href='{{ action('UsersController@edit', array($user->id)) }}'">@lang('buttons.edit')</button>
		</div>
	</div>

	<h4>@lang('groups.group_memberships'):</h4>
	<div class="well">
	    <ul>
	    	@if (count($groups) >= 1)
		    	@foreach ($groups as $group)
                    <li><a href="{{ URL::route('groups.show', [$group->id]) }}">{{ $group->name }}</a></li>
				@endforeach
			@else
				<li>@lang('groups.group_no_memberships').</li>
			@endif
	    </ul>
	</div>

    <h4>@lang('projects.projects'):</h4>
    <div class="well">
        <ul>
            @if (count($projects) >= 1)
            @foreach ($projects as $project)
            <li><a href="{{ URL::route('groups.projects.show', [$project->group_id, $project->id]) }}">{{ $project->title }}</a></li>
            @endforeach
            @else
            <li>@lang('projects.no_projects').</li>
            @endif
        </ul>
    </div>

    @if ($viewPermissions)
    <h4>@lang('pages.permissions_user'):</h4>
    <?php $userPermissions = $user->getPermissions(); ?>
    <div class="well">
        <ul>
            @if (count($userPermissions) >= 1)
            @foreach ($userPermissions as $key => $permission)
            <li>{{ str_replace('_', ' ', $key) }} {{ ($permission == 1) ? trans('pages.allowed') : trans('pages.denied') }}</li>
            @endforeach
            @else
            <li>@lang('pages.permissions_no').</li>
            @endif
        </ul>
    </div>
    @endif

@stop
