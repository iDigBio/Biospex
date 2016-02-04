@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_invite')
@stop

{{-- Content --}}
@section('content')
{!! Breadcrumbs::render('groups.get.read', $group) !!}
<div class="jumbotron">
    <h2>{{ trans('pages.group') }}: {{ $group->label }}</h2>
    <p>{{ trans('groups.invite_explained') }}</p>
</div>

<div class="col-xs-12">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans('groups.invite_title', ['group' => $group->label]) }}</h3>
        </div>
        <div class="panel-body">
            {!! Form::open([
                'route' => ['invites.post.store', $group->id],
                'method' => 'post',
                'class' => 'form-inline',
                'role' => 'form'
            ]) !!}
            @if (Session::get('errors'))
                @foreach (Session::get('errors')->get('emails') as $error)
                    {{ $error }}<br/>
                @endforeach
            @endif
            <div class="input-group col-xs-8 {{ ($errors->has('emails')) ? 'has-error' : '' }}">
                {!! Form::text('emails', null, ['class' => 'form-control', 'placeholder' => trans('groups.invite_emails')]) !!}
                <span class="input-group-btn">
                {!! Form::button('<i class="fa fa-envelope fa-lrg"></i> ' . trans('buttons.invite'), ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                </span>
            </div>
            <div class="form-group row">
                <em>{{ trans('groups.separate_emails') }} </em>
            </div>
            {!! Form::close() !!}

            @if ( ! $group->invites->isEmpty())
                <h4 class="top-margin">@lang('groups.existing_invites')</h4>
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
                                    <button class="btn btn-primary btn-sm" type="button" href="{!! route('invites.post.resend', [$invite->group_id, $invite->id]) !!}'" data-token="{{ csrf_token() }}" data-method="post"><span class="fa fa-envelope fa-lrg"></span> @lang('buttons.resend')</button>
                                    <button class="btn btn-default btn-danger action_confirm btn-sm" href="{{ route('invites.delete.delete', [$invite->group_id, $invite->id]) }}" data-token="{{ csrf_token() }}" data-method="delete"><span class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
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
