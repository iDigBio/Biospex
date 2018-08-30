@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.register')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top30">
        <!--   <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-4 col-md-offset-4"> -->
        <div class="col-xs-12 col-sm-6 col-sm-offset-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ trans('pages.event_registration') }}</h3>
                </div>
                <div class="panel-body">
                    {!! Form::open([
                    'route' => ['web.events.join-create', $team->uuid],
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    <div class="row">
                        <div class="col-md-10 col-md-offset-2">
                            <h4>@lang('pages.event'): {{ $team->event->title }}</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-2">
                            <h4>@lang('pages.team'): {{ $team->title }}</h4>
                        </div>
                    </div>
                    <div class="row top10">
                        <div class="col-md-6 col-md-offset-3">
                            <div class="form-group">
                                <div class="input-group {{ ($errors->has('nfn_user')) ? 'has-error' : '' }}">
                                    <span class="input-group-addon"><i class="fa fa-user fa-lg"></i></span>
                                    @if($active)
                                    {!! Form::text('nfn_user', '', ['id' => 'nfn_user', 'class' => 'form-control', 'placeholder' => trans('pages.nfn_user')]) !!}
                                    @else
                                        {!! Form::text('nfn_user', '', ['id' => 'nfn_user', 'class' => 'form-control', 'placeholder' => trans('pages.nfn_user_disabled'), 'disabled' => 'disabled']) !!}
                                        @endif
                                </div>
                                {{ $errors->first('nfn_user') }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-md-offset-4">
                            <div class="form-group">
                                {!! Honeypot::generate('registeruser', 'registertime') !!}
                                {!! Form::submit(trans('pages.register'), array('class' => 'btn btn-primary btn-block')) !!}
                                {!! Form::hidden('team_id', $team->id) !!}
                                {!! Form::hidden('uuid', $team->uuid) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop