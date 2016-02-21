@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.groups')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="jumbotron">
        <p>{{ trans('groups.group_explained') }}</p>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <th>@lang('pages.name')</th>
                    <th>@lang('groups.group_options')</th>
                </thead>
                <tbody>
                @foreach ($user->groups as $group)
                    <tr>
                        <td>{{ $group->label }}</td>
                        <td>
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
    </div>
    <div class="col-md-10 col-md-offset-1">
        <h4>{{ trans('groups.group_make') }}</h4>
        <button title="@lang('buttons.createTitleG')" class="btn btn-success" onClick="location.href='{{ route('groups.get.create') }}'"><span class="glyphicon glyphicon-plus"></span> @lang('buttons.create')</button>
    </div>
</div>
<!--  
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

