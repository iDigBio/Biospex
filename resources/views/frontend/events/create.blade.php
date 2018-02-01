@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('events.create')
@stop

{{-- Content --}}
@section('content')
    <div class="col-md-10 col-md-offset-1  top20">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('events.create') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => ['webauth.events.store'],
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                'class' => 'form-horizontal',
                'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('project_id')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('project_id', trans('forms.project'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('project_id') ? $errors->first('project_id') : '') }}
                        {!! Form::select('project_id', $projects, null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                    {!! Form::label('title', trans('forms.title'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => trans('forms.title')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}">
                    {!! Form::label('description_short', trans('forms.description'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description') ? $errors->first('description') : '') }}
                        {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => trans('forms.description_short_max')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                    {!! Form::label('contact', trans('forms.contact'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                        {!! Form::text('contact', null, ['class' => 'form-control', 'placeholder' => trans('forms.contact')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_email', trans('forms.contact_email'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                        {!! Form::text('contact_email', null, ['class' => 'form-control', 'placeholder' => trans('forms.contact_email')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('start_date')) ? 'has-error' : '' }}">
                    {!! Form::label('date', trans('forms.date'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="form-inline col-md-10">
                        <div class="input-group col-md-4">
                            {!! Form::label('start_date', trans('Start'), ['class' => 'control-label']) !!}
                            {!! Form::text('start_date', null, ['class' => 'form-control datetimepicker', 'placeholder' => trans('forms.event_timezone')]) !!}
                        </div>
                        <div class="input-group col-md-4">
                            {!! Form::label('end_date', trans('End'), ['class' => 'control-label']) !!}
                            {!! Form::text('end_date', null, ['class' => 'form-control datetimepicker', 'placeholder' => trans('forms.event_timezone')]) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('', trans('forms.event_groups'), ['class' => 'col-sm-2 control-label']) !!}
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
                        {!! Form::submit(trans('buttons.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop