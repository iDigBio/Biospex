@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('groups.group_view')
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('groups.get.show', $group) !!}
    <div class="jumbotron">
        <h2>{{ $group->label }}</h2>
    </div>

    <div class="panel panel-primary">
        <div style="padding: 10px;">
        @can('update', $group)
        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm"
                onClick="location.href='{{ route('groups.get.edit', array($group->id)) }}'"><span
                    class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
        <button title="@lang('buttons.inviteTitle')" class="btn btn-default btn-reverse btn-sm" type="button"
                onClick="location.href='{{ route('invites.get.index', [$group->id]) }}'"><span
                    class="fa fa-users fa-lrg"></span> @lang('buttons.invite')</button>
        @endcan
        @can('delete', $group)
        <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-sm"
                type="button" data-method="delete" data-token="{{ csrf_token() }}"
                href="{{ route('groups.delete.delete', array($group->id)) }}"><span
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
                            <li>{!! Html::mailto($user->email, $user->profile->first_name.' '.$user->profile->last_name) !!}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <ul>
                        @foreach ($group->projects as $project)
                            <li>{!! link_to_route('projects.get.show', $project->title, $project->id) !!}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@stop
