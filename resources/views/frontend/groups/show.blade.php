@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.group')
@endsection

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h3>{{ $group->title }}</h3>
    </div>

    <div class="panel panel-primary">
        <div style="padding: 10px;">
            @can('isOwner', $group)
                <button title="@lang('pages.editTitle')" class="btn btn-warning btn-sm"
                        onClick="location.href='{{ route('admin.groups.edit', array($group->id)) }}'"><span
                            class="fa fa-cog fa-lrg"></span> @lang('pages.edit')</button>
                <button title="@lang('pages.inviteTitle')" class="btn btn-default btn-reverse btn-sm" type="button"
                        onClick="location.href='{{ route('admin.invites.index', [$group->id]) }}'"><span
                            class="fa fa-users fa-lrg"></span> @lang('pages.invite')</button>
            @endcan
            @can('isOwner', $group)
                <button class="btn btn-sm btn-danger" title="@lang('pages.deleteTitle')"
                        data-href="{{ route('admin.groups.delete', array($group->id)) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will delete the item">
                    <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                </button>
            @endcan
        </div>
    </div>

    <div class="row">
        <table class="table">
            <thead>
            <tr>
                <th>@lang('pages.group') @lang('pages.owner')</th>
                <th>{{ trans('pages.users') }}</th>
                <th>{{ trans('pages.projects') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{!! Html::mailto($group->owner->email, $group->owner->present()->full_name_or_email) !!}</td>
                <td>
                    <ul class="list-group">
                        @foreach ($group->users as $user)
                            <li class="list-group-item">
                                <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                                        data-href="{{ route('admin.groups.deleteUser', [$group->id, $user->id]) }}"
                                        data-method="delete"
                                        data-toggle="confirmation"
                                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                                        data-btn-ok-class="btn-success"
                                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                                        data-btn-cancel-class="btn-danger"
                                        data-title="Continue action?" data-content="This will delete the item">
                                    <span class="fa fa-remove fa-sm"></span> <!-- @lang('pages.delete') -->
                                </button>
                                {!! Html::mailto($user->email, $user->present()->full_name_or_email) !!}
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <ul class="list-group">
                        @foreach ($group->projects as $project)
                            <li class="list-group-item">{!! link_to_route('admin.projects.show', $project->title, $project->id) !!}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
