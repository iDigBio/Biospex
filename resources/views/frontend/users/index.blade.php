@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('users.users')
@stop

{{-- Content --}}
@section('content')
    <h4>@lang('users.users_current'):</h4>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                    <th>User</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Options</th>
                    </thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <a href="{{ action('UsersController@show', array($user->id)) }}">{{ $user->profile->first_name }} {{ $user->profile->last_name }}</a>
                            </td>
                            <td>{{ HTML::mailto($user->email) }}</td>
                            <td>{{ $user->activated == 0 ? 'inactive' : 'active' }} </td>
                            <td>
                                <button class="btn btn-default" type="button"
                                        onClick="location.href='{{ action('UsersController@edit', array($user->id)) }}'">@lang('buttons.edit')</button>
                                @if ($user->suspended == 0)
                                    <button class="btn btn-default" type="button"
                                            onClick="location.href='{{ route('suspendUserForm', array($user->id)) }}'">@lang('buttons.suspend')</button>
                                @else
                                    <button class="btn btn-default" type="button"
                                            onClick="location.href='{{ action('UsersController@unsuspend', array($user->id)) }}'">@lang('buttons.unsuspend')</button>
                                @endif
                                @if ($user->banned == 0)
                                    <button class="btn btn-default" type="button"
                                            onClick="location.href='{{ action('UsersController@ban', array($user->id)) }}'">@lang('buttons.ban')</button>
                                @else
                                    <button class="btn btn-default" type="button"
                                            onClick="location.href='{{ action('UsersController@unban', array($user->id)) }}'">@lang('buttons.unban')</button>
                                @endif

                                <button class="btn btn-sm btn-danger" title="@lang('buttons.deleteTitle')"
                                        data-href="{{ action('UsersController@destroy', array($user->id)) }}"
                                        data-method="delete"
                                        data-toggle="confirmation"
                                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                                        data-btn-ok-class="btn-success"
                                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                                        data-btn-cancel-class="btn-danger"
                                        data-title="Continue action?" data-content="This will destroy the item">
                                    <span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
