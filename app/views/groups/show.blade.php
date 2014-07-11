@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_view')
@stop

{{-- Content --}}
@section('content')
<h4>{{ $group['name'] }} @lang('groups.group')</h4>
<div class="well clearfix">
    <div class="col-md-10">
        <strong>@lang('users.users'):</strong>
        <ul>
            @foreach ($group->users as $user)
            <li>{{ $user->first_name }} {{ $user->last_name }} {{ $user->email }}</li>
            @endforeach
        </ul>
    </div>
    <div class="col-md-10">
        <strong>@lang('projects.projects'):</strong>
        <ul>
            @foreach ($group->projects as $project)
            <li>{{ $project->title }}</li>
            @endforeach
        </ul>
    </div>
    @if ($viewPermissions)
	<div class="col-md-10">
	    <strong>@lang('pages.permissions'):</strong>
	    <ul>
	    	@foreach ($group->permissions as $key => $value)
	    		<li>{{ str_replace('_', ' ', ucfirst($key)) }}</li>
	    	@endforeach
	    </ul>
	</div>
    @endif
	<div class="col-md-2">
		<button class="btn btn-primary" onClick="location.href='{{ action('GroupsController@edit', array($group->id)) }}'">@lang('buttons.edit')</button>
	</div> 
</div>
@stop
