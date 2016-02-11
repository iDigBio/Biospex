@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_edit')
@stop

{{-- Content --}}
@section('content')
<div class="row">
{!! Breadcrumbs::render('groups.get.show.edit', $group) !!}
    <div class="row centered-form top-margin">
        <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('groups.group_edit') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => ['groups.put.update', $group->id],
                    'method' => 'put',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                                {!! Form::text('name', $group->label, ['class' => 'form-control', 'placeholder' => trans('pages.name'), 'required']) !!}
                            </div>
                            {{ ($errors->has('name') ?  $errors->first('name') : '') }}
                        </div>
                    </div>
                    {!! Form::hidden('id', $group->id) !!}
                    {!! Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary')) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop