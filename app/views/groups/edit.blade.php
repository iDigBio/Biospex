@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_edit')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    {{ Form::open(array('action' =>  array('GroupsController@update', $group->id), 'method' => 'put')) }}
    <div class="col-md-4 col-md-offset-4">
	{{ Form::open(array('action' =>  array('GroupsController@update', $group->id), 'method' => 'put')) }}
        <h2>@lang('groups.group_edit')</h2>

        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
            {{ Form::text('name', $group->name, array('class' => 'form-control', 'placeholder' => trans('pages.name'))) }}
            {{ ($errors->has('name') ? $errors->first('name') : '') }}
        </div>
        @if ($editPermissions)
            {{ Form::label(trans('pages.permissions')) }}
            <?php $groupPermissions = $group->permissions; ?>
            @foreach ($permissions as $key => $permission)
               @include('partials.editpermissions')
            @endforeach
        @endif

        {{ Form::hidden('id', $group->id) }}
        {{ Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary btn-xs')) }}

    {{ Form::close() }}
    </div>
</div>

@stop