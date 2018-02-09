@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.create') @lang('pages.events')
@stop

{{-- Content --}}
@section('content')
    <div class="col-md-10 col-md-offset-1  top20">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.create') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => ['webauth.events.store'],
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('project_id')) ? 'has-error' : '' }}">
                    {!! Form::label('project_id', trans('pages.project'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('project_id') ? $errors->first('project_id') : '') }}
                        {!! Form::select('project_id', $projects, null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}">
                    {!! Form::label('title', trans('pages.title'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => trans('pages.title')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}">
                    {!! Form::label('description_short', trans('pages.description'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description') ? $errors->first('description') : '') }}
                        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => trans('pages.description_short_max')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                    {!! Form::label('contact', trans('pages.contact'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                        {!! Form::text('contact', null, ['class' => 'form-control', 'placeholder' => trans('pages.contact')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_email', trans('pages.contact') . ' ' . trans('pages.email'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                        {!! Form::text('contact_email', null, ['class' => 'form-control', 'placeholder' => trans('pages.contact') . ' ' . trans('pages.email')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('start_date')) ? 'has-error' : '' }}">
                    {!! Form::label('date', trans('pages.date'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="form-inline col-md-10">
                        <div class="input-group col-md-4">
                            {!! Form::label('start_date', trans('pages.start_date'), ['class' => 'control-label']) !!}
                            {!! Form::text('start_date', null, ['class' => 'form-control datetimepicker', 'placeholder' => trans('pages.event_timezone')]) !!}
                        </div>
                        <div class="input-group col-md-4">
                            {!! Form::label('end_date', trans('pages.end_date'), ['class' => 'control-label']) !!}
                            {!! Form::text('end_date', null, ['class' => 'form-control datetimepicker', 'placeholder' => trans('pages.event_timezone')]) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('', trans('pages.event_groups'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="controls col-sm-10">
                        @if($errors->has('groups.*'))
                            @for($i = 0; $i < Input::old('groupsNum'); $i++)
                                @include('frontend.events.partials.group-error')
                            @endfor
                        @else
                            @include('frontend.events.partials.group-create')
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::hidden('groupsNum', 1) !!}
                        {!! Form::submit(trans('pages.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('pages.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop