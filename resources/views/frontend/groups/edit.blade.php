@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_edit')
@stop

{{-- Content --}}
@section('content')
<div class="row">
{!! Breadcrumbs::render('web.groups.show.edit', $group) !!}
    <div class="row centered-form ">
        <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('groups.group_edit') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => ['web.groups.update', $group->id],
                    'method' => 'put',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                                {!! Form::text('name', $group->label, ['class' => 'form-control', 'placeholder' => trans('pages.name'), 'required']) !!}
                                {{ ($errors->has('name') ?  $errors->first('name') : '') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group required {{ ($errors->has('user_id')) ? 'has-error' : '' }}" for="user_id">
                        {!! Form::label('user_id', trans('forms.owner'), ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::select('user_id', $users, $group->user_id, ['class' => 'selectpicker']) !!}
                        </div>
                        {{ ($errors->has('user_id') ? $errors->first('user_id') : '') }}
                    </div>
                    {!! Form::hidden('id', $group->id) !!}
                    {!! Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary')) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop