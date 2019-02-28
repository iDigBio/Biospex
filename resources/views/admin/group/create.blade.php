@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Create Group') }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <form id="gridForm" method="post"
                      action="{{ route('admin.groups.store') }}"
                      role="form" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <input type="hidden" value="{{ Auth::id() }}" name="owner" id="owner">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4">{{ __('CREATE GROUP') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('Title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>
                        @include('common.cancel-submit-buttons')
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop