@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('groups.group_invite')
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.groups.show.invite', $group) !!}
    <div class="jumbotron">
        <h2>{{ $group->title }}</h2>
        <p>{{ trans('groups.invite_explained') }}</p>
    </div>

    <div class="col-xs-8 col-lg-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('groups.invite_title', ['group' => $group->title]) }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                    'route' => ['web.invites.store', $group->id],
                    'method' => 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) !!}
                @if ($errors->any())
                    @foreach($errors->all() as $error)
                        <div class="red">{{$error}}</div>
                    @endforeach
                @endif
                <div class="form-group col-xs-12">
                    <em>{{ trans('groups.invite_form_text') }} </em>
                </div>
                <div class="controls">
                    <div class="entry">
                        <div class="form-group col-xs-6 pull-left">
                            <div class="input-group">
                                {!! Form::text('invites[][email]', null, ['class' => 'form-control', 'placeholder' => trans('groups.invite_email')]) !!}
                                <span class="input-group-btn">
                        {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
                        </span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-6">
                        {!! Form::button('<i class="fa fa-envelope fa-lrg"></i> ' . trans('buttons.invite'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                    </div>
                </div>
                {!! Form::close() !!}

                @if ( ! $group->invites->isEmpty())
                    <h4 class="">@lang('groups.existing_invites')</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>@lang('groups.email')</th>
                                <th class="nowrap">@lang('groups.invite_options')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($group->invites as $invite)
                                <tr>
                                    <td>{{ $invite->email }} </td>
                                    <td class="nowrap">
                                        <button class="btn btn-primary btn-sm" type="button"
                                                href="{!! route('web.invites.resend', [$invite->group_id, $invite->id]) !!}'"
                                                data-token="{{ csrf_token() }}" data-method="post"><span
                                                    class="fa fa-envelope fa-lrg"></span> @lang('buttons.resend')
                                        </button>
                                        <button class="btn btn-danger btn-sm" type="button"
                                                data-method="delete"
                                                data-toggle="confirmation" data-placement="left"
                                                data-href="{{ route('web.invites.delete', [$invite->group_id, $invite->id]) }}"><span
                                                    class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop
