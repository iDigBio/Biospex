@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.contact')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top30">
        <div class="col-xs-12 col-md-8 col-md-offset-2">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ __('pages.contact') }} {{ __('pages.biospex_team') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => 'home.post.contact',
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    <div class="col-xs-5">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('first_name')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-user fa-lg"></i></span>
                                {!! Form::text('first_name', old('first_name'), ['id' => 'first_name', 'class' => 'form-control', 'placeholder' => __('pages.first_name'),]) !!}
                            </div>
                            {{ $errors->first('first_name') }}
                        </div>
                    </div>
                    <div class="col-xs-2"></div>
                    <div class="col-xs-5">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('last_name')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-user fa-lg"></i></span>
                                {!! Form::text('last_name', old('last_name'), ['id' => 'last_name', 'class' => 'form-control', 'placeholder' => __('pages.last_name')]) !!}
                            </div>
                            {{ $errors->first('last_name') }}
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-envelope fa-lg"></i></span>
                                {!! Form::email('email', old('email'), ['id' => 'email', 'class' => 'form-control', 'placeholder' => __('pages.email')]) !!}
                            </div>
                            {{ $errors->first('email') }}
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <div class="input-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                                <span class="input-group-addon"><i class="fa fa-edit fa-lg"></i></span>
                                {!! Form::textarea('message', old('message'), ['id' => 'message', 'class' => 'form-control', 'placeholder' => __('pages.message')]) !!}
                            </div>
                            {{ $errors->first('email') }}
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                {!! Form::submit(__('pages.send'), array('class' => 'btn btn-primary')) !!}
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection