@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('edit') }} {{ __('group') }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-8 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <form id="gridForm" method="post"
                      action="{{ route('admin.groups.update', [$group->id]) }}"
                      role="form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" value="{{ Auth::id() }}" name="owner" id="owner">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4 text-uppercase">{{ __('pages.edit') }} {{ __('pages.group') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('pages.title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title', $group->title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="owner" class="col-form-label required">{{ __('pages.owner') }}:</label>
                        <select name="owner" id="owner"
                                class="form-control custom-select {{ ($errors->has('owner')) ? 'is-invalid' : '' }}"
                                required>
                            @foreach($users as $key => $name)
                                <option value="{{ $key }}" {{ $key === old('owner', Auth::id()) ?
                                        ' selected=selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback">{{ $errors->first('owner') }}</span>
                    </div>
                    @include('common.cancel-submit-buttons')
                </form>
            </div>
        </div>
    </div>
@stop