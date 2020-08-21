@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.rapid_import_title') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ __('pages.rapid_import_title') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-6 m-auto" id="import-accordion">
        <div class="card">
            <div class="card-header" id="file-import">
                <h5 class="mb-0">
                    <button class="import btn" data-toggle="collapse" data-target="#rapid-import"
                            aria-expanded="true" aria-controls="rapid-import">
                        {{ __('pages.rapid_import_data') }}
                    </button>
                </h5>
            </div>

            <div id="rapid-import" class="collapse {{ ($errors->has('import-file')) ? 'show' : '' }}"
                 aria-labelledby="file-import"
                 data-parent="#import-accordion">
                <div class="card-body">
                    <form action="{{ route('admin.import.create') }}"
                          method="post" role="form" class="form-horizontal" id="import-rapid-file"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <div class="custom-file">
                                <label class="custom-file-label"
                                       for="import-file">{{ __('pages.choose_file') }}</label>
                                <input type="file" name="import-file" class="custom-file-input"
                                       id="import-file"
                                       accept=".csv" required>
                                <span class="text-danger">{{ $errors->first('import-file') }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit"
                                    class="btn btn-primary pl-4 pr-4 text-uppercase">{{ __('pages.import') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="file-update">
                <h5 class="mb-0">
                    <button class="import btn" data-toggle="collapse" data-target="#rapid-update"
                            aria-expanded="false" aria-controls="rapid-update">
                        {{ __('pages.rapid_update_data') }}
                    </button>
                </h5>
            </div>

            <div id="rapid-update" class="collapse {{ ($errors->has('update-file')) ? 'show' : '' }}"
                 aria-labelledby="file-update"
                 data-parent="#import-accordion">
                <div class="card-body">
                    <form action="{{ route('admin.import.update') }}"
                          role="form" class="form-horizontal" id="update-rapid-file" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="form-group">
                            <div class="custom-file">
                                <label class="custom-file-label"
                                       for="update-file">{{ __('pages.choose_file') }}</label>
                                <input type="file" name="update-file" class="custom-file-input" id="update-file"
                                       accept=".csv" required>
                                <span class="text-danger">{{ $errors->first('update-file') }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit"
                                    class="btn btn-primary pl-4 pr-4 text-uppercase">{{ __('pages.update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection