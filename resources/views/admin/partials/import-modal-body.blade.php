<div id="import-accordion">
    <div class="card">
        <div class="card-header" id="dwc-file-import">
            <h5 class="mb-0">
                <button class="import btn" data-toggle="collapse" data-target="#dwc-upload"
                        aria-expanded="true" aria-controls="dwc-upload">
                    {{ __('pages.import_darwin_file') }}
                </button>
            </h5>
        </div>

        <div id="dwc-upload" class="collapse show" aria-labelledby="dwc-file-import"
             data-parent="#import-accordion">
            <div class="card-body">
                <form action="{{ route('admin.imports.dwcfile') }}"
                      method="post" role="form" class="form-horizontal" id="form-dwc-file" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="mt-2 mb-4">
                        <a href="#" class="btn btn-outline-primary collapsed preventDefault"
                           data-toggle="collapse" data-target="#dwc-file-instruction"
                           aria-expanded="true"
                           aria-controls="dwc-file-instruction">{{ __('pages.instructions') }}</a>
                        <span id="dwc-file-instruction" class="collapse">
                                            {{ __('pages.import_file_type') }}
                                            <a href="{{ Storage::url('public/darwin-core-example.zip') }}" class="link">{{ __('pages.import_file_tag') }}</a>
                                        </span>
                    </div>
                    <div class="custom-file">
                        <label class="custom-file-label"
                               for="customFile">{{ __('pages.choose_file') }}</label>
                        <input type="file" class="custom-file-input" id="dwc-file" accept=".zip" required>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit"
                                class="btn btn-primary pl-4 pr-4 text-uppercase">{{ __('pages.import') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="import card">
        <div class="card-header" id="record-set-import">
            <h5 class="mb-0">
                <button class="import btn collapsed" data-toggle="collapse"
                        data-target="#recordset" aria-expanded="false" aria-controls="recordset">
                    {{ __('pages.import_recordset') }}
                </button>
            </h5>
        </div>
        <div id="recordset" class="collapse" aria-labelledby="record-set-import"
             data-parent="#import-accordion">
            <div class="card-body">
                <form action="{{ route('admin.imports.recordset') }}"
                      method="post" role="form" class="form-horizontal" id="form-dwc-recordset" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="mt-2 mb-4">
                        <a href="#" class="btn btn-outline-primary collapsed preventDefault"
                           data-toggle="collapse" data-target="#recordset-instruction"
                           aria-expanded="true"
                           aria-controls="recordset-instruction">{{ __('pages.instructions') }}</a>
                        <span id="recordset-instruction" class="collapse">
                                            <ol class="mt-2">
                                                {{ __('html.import_recordset_desc') }}
                                            </ol>
                                        </span>
                    </div>
                    <div class="form-group">
                        <label for="recordset" class="mb-0">{{ __('page.import_recordset') }} <span
                                    class="color-action">*</span></label>
                        <input type="text" name="recordset" id="dwc-recordset" class="form-control" title="Must be valid UUID"
                               pattern="([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}){1}" required>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit"
                                class="btn btn-primary pl-4 pr-4 text-uppercase">{{ __('pages.import') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="import card">
        <div class="card-header" id="dwc-url-import">
            <h5 class="mb-0">
                <button class="import btn collapsed" data-toggle="collapse"
                        data-target="#dwc-uri" aria-expanded="false"
                        aria-controls="dwc-uri">
                    {{ __('pages.import_darwin_url') }}
                </button>
            </h5>
        </div>
        <div id="dwc-uri" class="collapse" aria-labelledby="dwc-url-import"
             data-parent="#import-accordion">
            <div class="card-body">
                <form action="{{ route('admin.imports.dwcuri') }}"
                      method="post" role="form" class="form-horizontal" id="form-dwc-url" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div class="mt-2 mb-4">
                        <a href="#" class="btn btn-outline-primary collapsed preventDefault"
                           data-toggle="collapse" data-target="#dwc-uri-instruction"
                           aria-expanded="true"
                           aria-controls="dwc-uri-instruction">{{ __('pages.instructions') }}</a>
                        <span id="dwc-uri-instruction" class="collapse">
                                            {{ __('pages.import_darwin_url_tag') }}
                                            <a href="{{ Storage::url('public/darwin-core-example.zip') }}" class="link">{{ __('pages.import_file_tag') }}</a>
                                        </span>
                    </div>
                    <div class="form-group">
                        <label for="data-url" class="mb-0">{{ __('URL') }} <span
                                    class="color-action">*</span></label>
                        <input type="url" name="dwc-url" id="dwc-url" class="form-control" required>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit"
                                class="btn btn-primary pl-4 pr-4 text-uppercase">{{ __('pages.import') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>