@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_edit')
@stop

{{-- Content --}}
@section('content')
<div class="row">
    
    <div class="col-md-4 col-md-offset-4">
	{{ Form::open(array('action' =>  array('GroupsController@update', $group->id), 'method' => 'put', 'class' => 'form-horizontal')) }}
        <legend>{{ trans('groups.group_edit') }}: {{{ $group->name }}}</legend>
        
        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
            {{ Form::label(trans('groups.group_name')) }}
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
        {{ Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}
    </div>
</div>

@stop