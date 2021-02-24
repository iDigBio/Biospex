@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Rapid Records Import') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Records Import') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-10 col-md-8 offset-md-1 m-auto">
            <div class="card white box-shadow py-5 my-5 p-sm-5">
                <div class="col-8 m-auto" id="import-accordion">
                    <div class="card">
                        <div class="card-header" id="file-import">
                            <h5 class="mb-0">
                                <button class="import btn" {{ $count > 0 ? 'disabled': '' }} data-toggle="collapse" data-target="#rapid-import"
                                        aria-expanded="true" aria-controls="rapid-import">
                                    {{ t('Import Rapid Records Data') }}
                                </button>
                            </h5>
                        </div>

                        <div id="rapid-import" class="collapse {{ ($errors->has('import-file')) ? 'show' : '' }}"
                             aria-labelledby="file-import"
                             data-parent="#import-accordion">
                            <div class="card-body">
                                <form action="{{ route('admin.ingest.create') }}"
                                      method="post" role="form" class="form-horizontal" id="import-rapid-file"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <div class="custom-file">
                                            <label class="custom-file-label"
                                                   for="import-file">{{ t('Choose file...') }}</label>
                                            <input type="file" name="import-file" class="custom-file-input"
                                                   id="import-file"
                                                   accept=".zip" required>
                                            <span class="text-danger">{{ $errors->first('import-file') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit"
                                                class="btn btn-primary pl-4 pr-4 text-uppercase">{{ t('Import') }}</button>
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
                                    {{ t('Update Rapid Records Data') }}
                                </button>
                            </h5>
                        </div>

                        <div id="rapid-update" class="collapse {{ ($errors->has('update-file')) ? 'show' : '' }}"
                             aria-labelledby="file-update"
                             data-parent="#import-accordion">
                            <div class="card-body">
                                <form action="{{ route('admin.ingest.update') }}"
                                      method="post" role="form" class="form-horizontal" id="importRapidFrm"
                                      enctype="multipart/form-data">
                                    @method('PUT')
                                    @csrf
                                    <div class="form-group">
                                        <div class="custom-file">
                                            <label class="custom-file-label"
                                                   for="update-file">{{ t('Choose file...') }}</label>
                                            <input type="file" name="update-file" class="custom-file-input" id="update-file"
                                                   accept=".zip" required>
                                            <span class="text-danger">{{ $errors->first('update-file') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit"
                                                class="btn btn-primary pl-4 pr-4 text-uppercase">{{ t('Update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection