@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('users.users')
@stop

{{-- Content --}}
@section('content')
<h4>@lang('users.users-current'):</h4>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead>
				<th>User</th>
				<th>Status</th>
				<th>Options</th>
			</thead>
			<tbody>
				@foreach ($users as $user)
					<tr>
						<td><a href="{{ action('UsersController@show', array($user->id)) }}">{{ $user->email }}</a></td>
						<td>{{ $user->status }} </td>
						<td>
							<button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('UsersController@edit', array($user->id)) }}'">@lang('buttons.edit')</button>
							@if ($user->status != 'Suspended')
								<button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ route('suspendUserForm', array($user->id)) }}'">@lang('buttons.suspend')</button>
							@else
								<button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('UsersController@unsuspend', array($user->id)) }}'">@lang('buttons.unsuspend')</button>
							@endif
							@if ($user->status != 'Banned')
								<button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('UsersController@ban', array($user->id)) }}'">@lang('buttons.ban')</button>
							@else
								<button class="btn btn-default btn-xs" type="button" onClick="location.href='{{ action('UsersController@unban', array($user->id)) }}'">@lang('buttons.unban')</button>
							@endif
							
							<button class="btn btn-default btn-danger btn-xs action_confirm" href="{{ action('UsersController@destroy', array($user->id)) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
  </div>
</div>
@stop
