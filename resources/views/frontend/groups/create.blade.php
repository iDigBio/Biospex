@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_create')
@stop

{{-- Content --}}
@section('content')
{!! Breadcrumbs::render('web.groups.show.create') !!}
<div class="row centered-form ">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">@lang('groups.group_create')</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => 'web.groups.store',
                'method' => 'post',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                            {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => trans('pages.name'), 'required']) !!}
                        </div>
                        {{ ($errors->has('title') ?  $errors->first('title') : '') }}
                    </div>
                </div>
                {!! Form::hidden('owner', $user->id) !!}
                {!!Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary')) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@stop