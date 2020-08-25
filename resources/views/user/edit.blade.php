@extends('layout.default')

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
                    <form id="gridForm" method="post"
                          action="{{ route('admin.users.update', [$user->id]) }}"
                          role="form" enctype="multipart/form-data">
                        {!! method_field('put') !!}
                        @csrf
                        <div class="form-group">
                            <label for="email" class="col-form-label required">{{ t('Email') }}:</label>
                            <input type="email" class="form-control {{ ($errors->has('email')) ? 'is-invalid' : '' }}"
                                   id="email" name="email"
                                   value="{{ old('email', $user->email) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                        </div>
                        @include('partials.cancel-submit-buttons')
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
                    <form id="gridForm" method="post"
                          action="{{ route('admin.users.password', [$user->id]) }}" role="form">
                        {!! method_field('put') !!}
                        @csrf
                        <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label for="oldPassword" class="col-form-label required">{{ t('Old Password') }}
                                :</label>
                            <input type="password"
                                   class="form-control {{ ($errors->has('oldPassword')) ? 'is-invalid' : '' }}"
                                   id="oldPassword" name="oldPassword"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('oldPassword') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="newPassword" class="col-form-label required">{{ t('New Password') }}
                                :</label>
                            <input type="password"
                                   class="form-control {{ ($errors->has('newPassword')) ? 'is-invalid' : '' }}"
                                   id="newPassword" name="newPassword"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('newPassword') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="newPassword_confirmation"
                                   class="col-form-label required">{{ t('Confirm Password') }}:</label>
                            <input type="password"
                                   class="form-control {{ ($errors->has('newPassword_confirmation')) ? 'is-invalid' : '' }}"
                                   id="newPassword_confirmation" name="newPassword_confirmation"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('newPassword_confirmation') }}</span>
                        </div>
                        @include('partials.cancel-submit-buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop