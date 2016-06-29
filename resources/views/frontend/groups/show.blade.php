@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('groups.group_view')
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.groups.show', $group) !!}
    <div class="jumbotron">
        <h3>{{ $group->name }}</h3>
    </div>

    <div class="panel panel-primary">
        <div style="padding: 10px;">
            @can('update', $group)
                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm"
                        onClick="location.href='{{ route('web.groups.edit', array($group->id)) }}'"><span
                            class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                <button title="@lang('buttons.inviteTitle')" class="btn btn-default btn-reverse btn-sm" type="button"
                        onClick="location.href='{{ route('web.invites.index', [$group->id]) }}'"><span
                            class="fa fa-users fa-lrg"></span> @lang('buttons.invite')</button>
            @endcan
            @can('delete', $group)
                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-sm delete-form" type="button"
                        data-method="delete"
                        data-confirm="Are you sure you wish to delete?"
                        data-href="{{ route('web.groups.delete', array($group->id)) }}"><span
                            class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover dataTable">
            <thead>
            <tr>
                <th>{{ trans('groups.group_owner') }}</th>
                <th>{{ trans('users.users') }}</th>
                <th>{{ trans('projects.projects') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{!! Html::mailto($group->owner->email, $group->owner->profile->first_name.' '.$group->owner->profile->last_name) !!}</td>
                <td>
                    <ul>
                        @foreach ($group->users as $user)
                            @if (null === $user->profile->first_name && null === $user->profile->last_name)
                                <li>{!! Html::mailto($user->email, $user->email) !!}</li>
                            @else
                                <li>{!! Html::mailto($user->email, $user->profile->first_name.' '.$user->profile->last_name) !!}</li>
                            @endif
                        @endforeach
                    </ul>
                </td>
                <td>
                    <ul>
                        @foreach ($group->projects as $project)
                            <li>{!! link_to_route('web.projects.show', $project->title, $project->id) !!}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@stop
