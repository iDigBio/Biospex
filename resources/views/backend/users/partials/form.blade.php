<div class="row">
    <!-- right column -->
    <div class="col-md-12">
        <div class="box box-primary {!! Html::collapse(['admin.users.edit', 'admin.users.*']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($editUser->id) ? 'Edit User' : 'Create User' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.users.edit', 'admin.users.*'], ['fa-minus', 'fa-plus']) !!}"></i>
                    </button>
                </h3>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="panel-body">
                    @if (isset($editUser->id))
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>{{ trans('pages.first_name') }}
                                        :</strong> {{ $editUser->profile->first_name }} </p>
                                <p><strong>{{ trans('pages.last_name') }}:</strong> {{ $editUser->profile->last_name }}
                                </p>
                                <p><strong>{{ trans('pages.email') }}:</strong> {{ $editUser->email }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><em>{{ trans('pages.account_created') }}
                                        : {{ DateHelper::formatDate($editUser->created_at, 'Y-m-d', $editUser->profile->timezone) }}</em>
                                </p>
                                <p><em>{{ trans('pages.last_updated') }}
                                        : {{ DateHelper::formatDate($editUser->updated_at, 'Y-m-d', $editUser->profile->timezone) }}</em>
                                </p>
                            </div>
                        </div>
                    @endif
                    {!! Form::open([
                    'route' => isset($editUser->id) ? ['admin.users.update', $editUser->id] : ['admin.users.store'],
                    'method' => isset($editUser->id) ? 'put' : 'post',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    <div class="form-group clearfix required {{ ($errors->has('first_name')) ? 'has-error' : '' }}"
                         for="first_name">
                        {!! Form::label('edit_first_name', trans('pages.first_name'), ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-6">
                            {!! Form::text('first_name', isset($editUser->profile->first_name) ? $editUser->profile->first_name : null, array('class' => 'form-control', 'placeholder' => trans('pages.first_name'))) !!}
                        </div>
                        {{ ($errors->has('first_name') ? $errors->first('first_name') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('last_name')) ? 'has-error' : '' }}"
                         for="last_name">
                        {!! Form::label('edit_last_name', trans('pages.last_name'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-6">
                            {!! Form::text('last_name', isset($editUser->profile->last_name) ? $editUser->profile->last_name : null, array('class' => 'form-control', 'placeholder' => trans('pages.last_name'), 'id' => 'edit_last_name')) !!}
                        </div>
                        {{ ($errors->has('last_name') ? $errors->first('last_name') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('email')) ? 'has-error' : '' }}" for="email">
                        {!! Form::label('edit_email', trans('pages.email'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-6">
                            {!! Form::text('email', isset($editUser->email) ? $editUser->email : null, array('class' => 'form-control', 'placeholder' => trans('pages.email'), 'id' => 'edit_email')) !!}
                        </div>
                        {{ ($errors->has('email') ? $errors->first('email') : '') }}
                    </div>

                    @if ( ! isset($editUser->id))
                    <div class="form-group required {{ $errors->has('newPassword') ? 'has-error' : '' }}">
                        {!! Form::label('newPassword', trans('pages.password_new'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-6">
                        {!! Form::password('newPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_new'))) !!}
                        </div>
                        {{ ($errors->has('newPassword') ? $errors->first('newPassword') : '') }}
                    </div>
                    <div class="form-group requred {{ $errors->has('newPassword_confirmation') ? 'has-error' : '' }}">
                        {!! Form::label('newPassword_confirmation', trans('pages.password_new_confirm'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-6">
                        {!! Form::password('newPassword_confirmation', array('class' => 'form-control', 'placeholder' => trans('pages.password_new_confirm'))) !!}
                        </div>
                        {{ ($errors->has('newPassword_confirmation') ? $errors->first('newPassword_confirmation') : '') }}
                    </div>
                    @endif

                    <div class="form-group {{ ($errors->has('timezone')) ? 'has-error' : '' }}" for="timezone">
                        {!! Form::label('timezone', trans('forms.timezone'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-6">
                            {!! Form::select('timezone', $timezones, isset($editUser->profile->timezone) ? $editUser->profile->timezone : 'America/New_York', array('class' => 'form-control')) !!}
                        </div>
                        {{ ($errors->has('timezone') ? $errors->first('timezone') : '') }}
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>

                @if (isset($editUser->id))
                    <div class="panel-body">
                        <h4>@lang('pages.password_change')</h4>
                        {!! Form::open([
                            'route' => ['admin.users.pass', $editUser->id],
                            'method' => 'put',
                            'class' => 'form-inline',
                            'role' => 'form'
                            ]) !!}
                        <div class="form-group required {{ $errors->has('newPassword') ? 'has-error' : '' }}">
                            {!! Form::label('newPassword', trans('pages.password_new'), array('class' => 'sr-only')) !!}
                            {!! Form::password('newPassword', array('class' => 'form-control', 'placeholder' => trans('pages.password_new'))) !!}
                        </div>

                        <div class="form-group requred {{ $errors->has('newPassword_confirmation') ? 'has-error' : '' }}">
                            {!! Form::label('newPassword_confirmation', trans('pages.password_new_confirm'), array('class' => 'sr-only')) !!}
                            {!! Form::password('newPassword_confirmation', array('class' => 'form-control', 'placeholder' => trans('pages.password_new_confirm'))) !!}
                        </div>
                        {!! Form::hidden('admin', true) !!}
                        {!! Form::submit('Submit', array('class' => 'btn btn-primary')) !!}

                        {!! ($errors->has('oldPassword') ? '<br />' . $errors->first('oldPassword') : '') !!}
                        {!! ($errors->has('newPassword') ?  '<br />' . $errors->first('newPassword') : '') !!}
                        {!! ($errors->has('newPassword_confirmation') ? '<br />' . $errors->first('newPassword_confirmation') : '') !!}

                        {!! Form::close() !!}
                    </div>
                @endif
            </div>
            <!-- /.box -->
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>