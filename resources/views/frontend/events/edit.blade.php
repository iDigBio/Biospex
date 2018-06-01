@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.edit') @lang('pages.events')
@stop

{{-- Content --}}
@section('content')
    <div class="col-md-10 col-md-offset-1  top20">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">@lang('pages.edit') @lang('pages.event')</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                'route' => ['webauth.events.update', $event->id],
                    'method' => 'put',
                    'files' => true,
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) !!}
                {!! method_field('put') !!}

                <div class="form-group required {{ ($errors->has('project_id')) ? 'has-error' : '' }}">
                    {!! Form::label('project_id', trans('pages.project'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('project_id') ? $errors->first('project_id') : '') }}
                        {!! Form::select('project_id', $projects, $event->project_id, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}">
                    {!! Form::label('title', trans('pages.title'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        {!! Form::text('title', $event->title, ['class' => 'form-control', 'placeholder' => trans('pages.title')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}">
                    {!! Form::label('description_short', trans('pages.description'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description') ? $errors->first('description') : '') }}
                        {!! Form::text('description', $event->description, ['class' => 'form-control', 'placeholder' => trans('pages.description_short_max')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                    {!! Form::label('contact', trans('pages.contact'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                        {!! Form::text('contact', $event->contact, ['class' => 'form-control', 'placeholder' => trans('pages.contact')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_email', trans('pages.contact') . ' ' . trans('pages.email'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                        {!! Form::text('contact_email', $event->contact_email, ['class' => 'form-control', 'placeholder' => trans('pages.contact') . ' ' . trans('pages.email')]) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('start_date')) || ($errors->has('end_date')) ? 'has-error' : '' }}">
                    {!! Form::label('date', trans('pages.date'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="form-inline col-md-10">
                        <div class="input-group col-md-3">
                            {{ ($errors->has('start_date') ? $errors->first('start_date') : '') }}
                            {!! Form::label('start_date', trans('pages.start_date'), ['class' => 'control-label']) !!}
                            {!! Form::text('start_date', $event->start_date->setTimezone($event->timezone)->format('Y-m-d H:i'), ['class' => 'form-control datetimepicker', 'placeholder' => trans('pages.event_timezone')]) !!}
                        </div>
                        <div class="input-group col-md-3">
                            {{ ($errors->has('end_date') ? $errors->first('end_date') : '') }}
                            {!! Form::label('end_date', trans('pages.end_date'), ['class' => 'control-label']) !!}
                            {!! Form::text('end_date', $event->end_date->setTimezone($event->timezone)->format('Y-m-d H:i'), ['class' => 'form-control datetimepicker', 'placeholder' => trans('pages.event_timezone')]) !!}
                        </div>
                        <div class="input-group col-md-4">
                            {!! Form::label('timezone', trans('pages.timezone'), ['class' => 'control-label']) !!}
                            {!! Form::select('timezone', $timezones, $event->timezone, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('', trans('pages.event_groups'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="controls col-sm-10">
                        @if($errors->has('groups.*'))
                            @for($i = 0; $i < old('entries'); $i++)
                                @include('frontend.events.partials.group-error')
                            @endfor
                        @elseif($event->groups->isNotEmpty())
                            @foreach($event->groups as $key => $group)
                                @include('frontend.events.partials.group-edit')
                            @endforeach
                        @else
                            @include('frontend.events.partials.group-create')
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::hidden('owner_id', Auth::id()) !!}
                        {!! Form::hidden('entries', $event->groups->count() === 0 ? 1 : $event->groups->count()) !!}
                        {!! Form::submit(trans('pages.update'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('pages.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop