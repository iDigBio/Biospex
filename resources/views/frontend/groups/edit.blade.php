@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('groups.group_edit')
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        {!! Breadcrumbs::render('web.groups.show.edit', $group) !!}
        <div class="row centered-form ">
            <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ trans('groups.group_edit') }}</h3>
                    </div>
                    <div class="panel-body">
                        {!! Form::open([
                        'route' => ['web.groups.update', $group->id],
                        'method' => 'put',
                        'class' => 'form-horizontal',
                        'role' => 'form'
                        ]) !!}
                        <div class="col-xs-12">
                            <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="name">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                                    {!! Form::text('title', old('title') ?: $group->title, ['class' => 'form-control', 'placeholder' => trans('pages.name')]) !!}
                                </div>
                                {{ ($errors->has('title') ?  $errors->first('title') : '') }}
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="form-group required {{ ($errors->has('owner')) ? 'has-error' : '' }}" for="owner">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user fa-lg" title="Group Owner"></i></span>
                                    {!! Form::select('owner', $users, $group->owner->id, ['class' => 'form-control']) !!}
                                </div>
                                {{ ($errors->has('owner') ? $errors->first('owner') : '') }}
                            </div>
                        </div>
                        {!! Form::submit(trans('buttons.update'), ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
@stop