@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.contact')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.contact_biospex') }}</h3>
                </div>
                <div class="panel-body">
                    {{ Form::open(array('action' => 'HomeController@sendContactForm')) }}
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::text('first_name', Input::old('first_name'), ['id' => 'first_name', 'class' => 'form-control', 'placeholder' => trans('pages.first_name'),])}}
                                </div>
                                {{$errors->first('first_name')}}
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    {{Form::text('last_name', Input::old('last_name'), ['id' => 'last_name', 'class' => 'form-control', 'placeholder' => trans('pages.last_name')])}}
                                </div>
                                {{$errors->first('last_name')}}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            {{Form::email('email', Input::old('email'), ['id' => 'email', 'class' => 'form-control', 'placeholder' => trans('pages.email')])}}
                        </div>
                        {{$errors->first('email')}}
                    </div>
                    <div class="form-group">
                        <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-edit"></i></span>
                            {{Form::textarea('message', Input::old('message'), ['id' => 'message', 'class' => 'form-control', 'placeholder' => trans('pages.message')])}}
                        </div>
                        {{$errors->first('email')}}
                    </div>

                    {{ Honeypot::generate('registeruser', 'registertime') }}
                    {{ Form::submit(trans('buttons.send'), array('class' => 'btn btn-primary btn-block')) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@stop