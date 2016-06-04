@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.groups')
@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h3>{{ trans('groups.groups') }}
            <button title="@lang('buttons.createTitleG')" class="btn btn-success"
                    onClick="location.href='{{ route('groups.get.create') }}'"><span
                        class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
        </h3>
    </div>
    <div class="col-md-12">
            <table class="table-sort th-center">
                <thead>
                    <th>@lang('pages.name')</th>
                    <th class="nowrap sorter-false">@lang('groups.group_options')</th>
                </thead>
                <tbody>
                @foreach ($user->groups as $group)
                    <tr>
                        <td>{{ $group->label }}</td>
                        <td class="buttons-sm">
                            <button title="@lang('buttons.viewTitle')" class="btn btn-default btn-primary btn-sm" type="button" onClick="location.href='{{ route('groups.get.show', [$group->id]) }}'"><span class="fa fa-eye fa-lrg"></span> @lang('buttons.view')</button>
                            @can('update', $group)
                            <button title="@lang('buttons.editTitle')" class="btn btn-default btn-warning btn-sm" type="button" onClick="location.href='{{ route('groups.get.edit', array($group->id)) }}'"><span class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
                            <button title="@lang('buttons.inviteTitle')" class="btn btn-default btn-reverse btn-sm" type="button" onClick="location.href='{{ route('invites.get.index', [$group->id]) }}'"><span class="fa fa-users fa-lrg"></span> @lang('buttons.invite')</button>
                            @endcan
                            @can('delete', $group)
                            <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-sm" type="button" data-method="delete" data-token="{{ csrf_token() }}" href="{{ route('groups.delete.delete', array($group->id)) }}"><span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
    </div>
@stop

