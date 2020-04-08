@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.create') }} {{ __('pages.bingo') }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <div class="col-12">
                    <h2 class="text-center content-header text-uppercase mb-4">{{ __('pages.create') }} {{ __('pages.bingo') }}</h2>
                    <form id="gridForm" method="post"
                          action="{{ route('admin.bingos.store') }}"
                          role="form" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="entries" value="{{ old('entries', 1) }}">
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        <div class="form-group">
                            <div class="col-12 p-0">
                                <label for="project_id" class="col-form-label required">{{ __('pages.project') }}:</label>
                            </div>
                            <div class="col-6 p-0">
                                <select name="project_id" id="project_id"
                                        class="form-control custom-select {{ ($errors->has('project_id')) ? 'is-invalid' : '' }}"
                                        required>
                                    @foreach($projects as $key => $title)
                                        <option {{ $key == old('project_id') ?
                                        ' selected=selected' : '' }} value="{{ $key }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('pages.title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="directions" class="col-form-label required">{{ __('pages.directions') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('directions')) ? 'is-invalid' : '' }}"
                                   id="directions" name="directions"
                                   value="{{ old('directions') }}"
                                   placeholder="Between 6 and 250 characters" required>
                            <span class="invalid-feedback">{{ $errors->first('directions') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="words" class="col-form-label">{{ __('pages.words') }}:</label>
                            @include('admin.bingo.partials.words', ['words' => null])
                        </div>
                        @include('common.cancel-submit-buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop