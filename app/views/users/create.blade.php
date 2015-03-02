@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('pages.create')
@stop

{{-- Content --}}
@section('content')
<div class="row centered-form">
    <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.create_account') }}</h3>
            </div>
            <div class="panel-body">
                {{ Form::open(array('action' => 'UsersController@store')) }}
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::text('first_name', '', ['id' => 'first_name', 'class' => 'form-control', 'placeholder' => trans('pages.first_name'),])}}
                                </div>
                                {{$errors->first('first_name')}}
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::text('last_name', '', ['id' => 'last_name', 'class' => 'form-control', 'placeholder' => trans('pages.last_name')])}}
                                </div>
                                {{$errors->first('last_name')}}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            {{Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('pages.email')])}}
                        </div>
                        {{$errors->first('email')}}
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                    {{Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => trans('pages.password')])}}
                                </div>
                                {{$errors->first('password')}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                    {{Form::password('password_confirmation', ['class' => 'form-control', 'id' =>'password_confirmation', 'placeholder' => trans('pages.password_confirmation')])}}
                                </div>
                                {{$errors->first('password_confirmation')}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 collapse" id="groupInput">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('new_group')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::password('new_group', ['class' => 'form-control', 'id' =>'new_group', 'placeholder' => trans('pages.new_group')])}}
                                </div>
                                {{$errors->first('new_group')}}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="input-group {{ ($errors->has('group')) ? 'has-error' : '' }}">
                                <div class="form-group">
                                    {{ Form::select('group', ['' => 'Select Group', 'new' => "New"] + $groups, Input::old('group'), ['id' => 'userGroup']) }}
                                </div>
                                {{$errors->first('group')}}
                            </div>
                        </div>
                    </div>
                    {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary btn-block')) }}
                    {{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-block btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@stop