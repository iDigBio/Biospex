@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.group') @lang('pages.invite')
@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h2>{{ $group->title }}</h2>
        <p>{{ trans('pages.invite_explained') }}</p>
    </div>

    <div class="col-xs-8 col-lg-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.invite_title', ['group' => $group->title]) }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                    'route' => ['admin.invites.store', $group->id],
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
                    <em>{{ trans('pages.invite_form_text') }} </em>
                </div>
                <div class="controls">
                    <div class="entry">
                        <div class="form-group col-xs-6 pull-left">
                            <div class="input-group">
                                {!! Form::text('invites[][email]', null, ['class' => 'form-control', 'placeholder' => trans('pages.email')]) !!}
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
                        {!! Form::button('<i class="fa fa-envelope fa-lrg"></i> ' . trans('pages.invite'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                    </div>
                </div>
                {!! Form::close() !!}

                @if ( ! $group->invites->isEmpty())
                    <h4 class="">@lang('pages.existing_invites')</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>@lang('pages.email')</th>
                                <th class="nowrap">@lang('pages.options')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($group->invites as $invite)
                                <tr>
                                    <td>{{ $invite->email }} </td>
                                    <td class="nowrap">
                                        <button class="btn btn-small btn-primary"
                                                data-href="{!! route('admin.invites.resend', [$invite->group_id, $invite->id]) !!}"
                                                data-method="post"
                                                data-toggle="confirmation"
                                                data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                                                data-btn-ok-class="btn-success"
                                                data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                                                data-btn-cancel-class="btn-danger"
                                                data-title="Continue action?" data-content="This will resend the invite">
                                            <span class="fa fa-envelope fa-lrg"></span> @lang('pages.resend')
                                        </button>

                                        <button class="btn btn-small btn-danger"
                                                data-href="{{ route('admin.invites.delete', [$invite->group_id, $invite->id]) }}"
                                                data-method="delete"
                                                data-toggle="confirmation"
                                                data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-lrg fa-share"
                                                data-btn-ok-class="btn-success"
                                                data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-lrg fa-ban"
                                                data-btn-cancel-class="btn-danger"
                                                data-title="Continue action?" data-content="This will delete the invite">
                                            <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                                        </button>
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
