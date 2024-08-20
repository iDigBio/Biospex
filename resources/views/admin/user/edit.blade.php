@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Edit Account') }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-8 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <div class="col-12">
                    <h2 class="text-center content-header mb-4 text-uppercase">{{ t('Edit Account') }}</h2>
                    <form method="post"
                          action="{{ route('admin.users.update', ['users' => $user->id]) }}"
                          role="form" enctype="multipart/form-data">
                        {!! method_field('put') !!}
                        @csrf
                        <div class="form-group">
                            <label for="first_name" class="col-form-label required">{{ t('First Name') }}
                                :</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('first_name')) ? 'is-invalid' : '' }}"
                                   id="first_name" name="first_name"
                                   value="{{ old('first_name', $user->profile->first_name) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('first_name') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="col-form-label required">{{ t('Last Name') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('last_name')) ? 'is-invalid' : '' }}"
                                   id="last_name" name="last_name"
                                   value="{{ old('last_name', $user->profile->last_name) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('last_name') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-form-label required">{{ t('Email') }}:</label>
                            <input type="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}"
                                   id="email" name="email"
                                   value="{{ old('email', $user->email) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                        </div>
                        <div class="form-group">
                            <div class="col-12 p-0">
                                <label for="timezone" class="col-form-label required">{{ t('Timezone') }}
                                    :</label>
                            </div>
                            <div class="col-6 p-0">
                                <select name="timezone" id="timezone"
                                        class="form-control custom-select {{ ($errors->has('timezone')) ? 'is-invalid' : '' }}"
                                        required>
                                    @foreach($timezones as $key => $value)
                                        <option {{ $key == old('timezone', $user->profile->timezone) ?
                                        ' selected=selected' : '' }} value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notification" class="form-check-inline col-form-label">
                                <input class="form-check-input" type="checkbox" name="notification"
                                       value="{{ old('notification', $user->notification) }}" {{ $user->notification === 1 ? "checked" : "" }}>
                                Notification (Receive notifications for projects you belong to)
                            </label>
                        </div>
                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4">
                                <div class="custom-file">
                                    <label for="avatar" class="custom-file-label">{{ t('Avatar') }}:</label>
                                    <input type="file"
                                           class="form-control custom-file-input {{ ($errors->has('avatar')) ? 'is-invalid' : '' }}"
                                           name="avatar" id="avatar"
                                           accept="image/png, image/jpg">
                                    <span class="invalid-feedback">{{ $errors->first('avatar') }}</span>
                                </div>
                            </div>
                            <input type="hidden" name="current_avatar" value="{{ $user->profile->avatar_file_name }}">
                            <div class="form-group col-sm-6">
                                <img alt="profile avatar" class="img-fluid" style="display: inline; width: 100px; height: 100px;"
                                     src="{{ $user->profile->avatar->url('medium') }}"/>
                            </div>
                        </div>
                        @include('common.cancel-submit-buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <div class="col-12">
                    <h2 class="text-center content-header mb-4 text-uppercase">{{ t('Change Password') }}</h2>
                    <form method="post"
                          action="{{ route('admin.users.password', ['users' => $user->id]) }}" role="form">
                        {!! method_field('put') !!}
                        @csrf
                        <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label for="current_password" class="col-form-label required">{{ t('Old Password') }}
                                :</label>
                            <input type="password"
                                   class="form-control {{ ($errors->has('current_password')) ? 'is-invalid' : '' }}"
                                   id="current_password" name="current_password"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('current_password') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="password" class="col-form-label required">{{ t('New Password') }}
                                :</label>
                            <input type="password"
                                   class="form-control {{ ($errors->has('password')) ? 'is-invalid' : '' }}"
                                   id="password" name="password"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation"
                                   class="col-form-label required">{{ t('Confirm Password') }}:</label>
                            <input type="password"
                                   class="form-control {{ ($errors->has('password_confirmation')) ? 'is-invalid' : '' }}"
                                   id="password_confirmation" name="password_confirmation"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('password_confirmation') }}</span>
                        </div>
                        @include('common.cancel-submit-buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop