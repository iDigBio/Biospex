@extends('backend.layouts.app')

@section('htmlheader_title')
    {{ $group->name }}
@endsection

@section('contentheader_title', 'Manage ' . $group->name)


@section('main-content')
    <div class="row">
        <div class="col-md-4">
            <div class="box box-widget widget-user">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-aqua-active">
                    <h3>Group Edit</h3>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="nav nav-stacked">
                                <li>
                                    <strong>Owner: </strong>{{ $group->owner->profile->full_name }}
                                </li>
                                <li>
                                    <strong>Email: </strong>{{ $group->owner->email }}
                                </li>
                            </ul>
                            {!! Form::open([
                            'route' => ['admin.groups.update', $group->id],
                            'method' => 'put',
                            'class' => 'form-horizontal',
                            'role' => 'form'
                            ]) !!}
                            <div class="col-xs-12">
                                <div class="form-group required {{ ($errors->has('name')) ? 'has-error' : '' }}"
                                     for="name">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                                        {!! Form::text('name', old('name') ?: $group->name, ['class' => 'form-control', 'placeholder' => trans('pages.name')]) !!}
                                    </div>
                                    {{ ($errors->has('name') ?  $errors->first('name') : '') }}
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group required {{ ($errors->has('owner')) ? 'has-error' : '' }}"
                                     for="owner">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-user fa-lg"
                                                                           title="Group Owner"></i></span>
                                        {!! Form::select('owner', $group->users->pluck('email', 'id'), $group->owner->id, ['class' => 'selectpicker form-control']) !!}
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
        </div>
        <div class="col-md-4">
            <div class="box box-widget widget-user">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-aqua-active">
                    <h3>Users</h3>
                </div>
                <div class="box-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="contacts-list">
                                @foreach($group->users as $user)
                                    <li>
                                        <div class="contacts-list-info">
                                        <span class="contacts-list-name">
                                            {{  $user->profile->full_name }}
                                            <small class="contacts-list-date pull-right">
                                                <button title="@lang('buttons.deleteTitle')"
                                                        class="btn btn-danger btn-xs" type="button"
                                                        data-toggle="confirmation" data-placement="left"
                                                        data-href="{{ route('admin.groups.deleteUser', [$group->id, $user->id]) }}"
                                                        data-method="delete">
                                                <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
                                            </small>
                                        </span>
                                            <span class="contacts-list-msg">{{  $user->email }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::open([
                            'route' => ['admin.groups.invite', $group->id],
                            'method' => 'post',
                            'class' => 'form-horizontal',
                            'role' => 'form'
                            ]) !!}
                            @if ($errors->any())
                                @foreach($errors->all() as $error)
                                    <div class="red">{{$error}}</div>
                                @endforeach
                            @endif
                            <div class="col-xs-12">
                                <div class="form-group required" for="invites">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-user fa-lg" title="Invites"></i></span>
                                        {!! Form::select('invites[][email]', [], null, ['class' => 'users-ajax form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            {!! Form::submit(trans('buttons.invite'), ['class' => 'btn btn-primary']) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection