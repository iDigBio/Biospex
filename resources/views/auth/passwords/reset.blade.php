@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.password_reset')
@stop

@section('content')
    <div class="row top30">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.password_reset') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => 'app.password.request',
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    {!! Form::hidden('token', $token) !!}
                    <div class="col-md-12">
                        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                                {!! Form::email('email', '', ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Email address']) !!}
                            </div>
                            {{ ($errors->has('email') ?  $errors->first('email') : '') }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('password')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-lock fa-lg"></i></span>
                                {!! Form::password('password', ['id' => 'password', 'class' => 'form-control', 'placeholder' => trans('pages.password')]) !!}
                            </div>
                            {{ $errors->first('password') }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-lock fa-lg"></i></span>
                                {!! Form::password('password_confirmation', ['class' => 'form-control', 'id' =>'password_confirmation', 'placeholder' => trans('pages.password_confirmation')]) !!}
                            </div>
                            {{ $errors->first('password_confirmation') }}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-3">
                            {!! Form::submit(trans('pages.password_reset'), array('class' => 'btn btn-primary btn-block')) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                    <div class="col-md-6 col-md-offset-3 top15 text-center">
                        {!! link_to_route('app.get.login', trans('pages.back_to_login')) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
