<!-- Modal -->
<div class="modal fade" id="import-modal" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="empty-icon"><i class="fa fa-fw"></i></div>
                <div><h2 class="color-action">{{ __('PROJECT_SUBJECT IMPORT') }}</h2></div>
                <div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div id="import-accordion">
                    <div class="import card">
                        <div class="card-header" id="dwc-file-import">
                            <h5 class="mb-0">
                                <button class="import btn btn-link" data-toggle="collapse" data-target="#dwc-upload"
                                        aria-expanded="true" aria-controls="dwc-upload">
                                    {{ __('Import Darwin Core File') }}
                                </button>
                            </h5>
                        </div>

                        <div id="dwc-upload" class="collapse show" aria-labelledby="dwc-file-import"
                             data-parent="#import-accordion">
                            <div class="card-body">
                                <form action="{{ route('admin.imports.dwcfile', ['projectId' => $project->id]) }}"
                                      method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="mt-2 mb-4">
                                        <a href="#" class="btn btn-outline-primary collapsed preventDefault"
                                                data-toggle="collapse" data-target="#dwc-file-instruction"
                                                aria-expanded="true"
                                                aria-controls="dwc-file-instruction">{{ __('Instructions') }}</a>
                                        <span id="dwc-file-instruction" class="collapse">
                                            {{ __('Only zipped Darwin Core files are accepted.') }}
                                            <a href="{{ Storage::url('public/darwin-core-example.zip') }}" class="link">{{ __('Download Example DWC File.') }}</a>
                                        </span>
                                    </div>
                                    <div class="custom-file">
                                        <label class="custom-file-label"
                                               for="customFile">{{ __('Choose file...') }}</label>
                                        <input type="file" class="custom-file-input" id="dwcFile">
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit"
                                                class="btn btn-primary pl-4 pr-4">{{ __('UPLOAD') }}</button>
                                        {!! Honeypot::generate('formuser', 'formtime') !!}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="import card">
                        <div class="card-header" id="record-set-import">
                            <h5 class="mb-0">
                                <button class="import btn btn-link collapsed" data-toggle="collapse"
                                        data-target="#recordset" aria-expanded="false" aria-controls="recordset">
                                    {{ __('Import Using Record Set Id') }}
                                </button>
                            </h5>
                        </div>
                        <div id="recordset" class="collapse" aria-labelledby="record-set-import"
                             data-parent="#import-accordion">
                            <div class="card-body">
                                <form action="{{ route('admin.imports.recordset', ['projectId' => $project->id]) }}"
                                      method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="mt-2 mb-4">
                                        <a href="#" class="btn btn-outline-primary collapsed preventDefault"
                                                data-toggle="collapse" data-target="#recordset-instruction"
                                                aria-expanded="true"
                                                aria-controls="recordset-instruction">{{ __('Instructions') }}</a>
                                        <span id="recordset-instruction" class="collapse">
                                            <ol class="mt-2">
                                                <li>{{ __('Go to') }} <a href="https://www.idigbio.org/portal/publishers"
                                                             target="_blank" class="link">{{ __('iDigBio.org Publishers Page') }}</a></li>
                                                <li>{{ __('Find the Publisher you want and select. (e.g. https://herbarium.bio.fsu.edu:8443/)') }}</li>
                                                <li>{{ __('Click the Collection you are interested in. (e.g. Robert K. Godfrey Herbarium at Florida State University)') }}</li>
                                                <li>{{ __('iDiogBio does not actually show the recordset id in the page, so it must be retrieved via the URL.') }}
                                                    <ol>
                                                        <li>{{ __('Url: https://www.idigbio.org/portal/recordsets/b2b294ed-1742-4479-b0c8-a8891fccd7eb') }}</li>
                                                        <li>{{ __('Record Id: b2b294ed-1742-4479-b0c8-a8891fccd7eb') }}</li>
                                                    </ol>
                                                </li>
                                            </ol>
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="recordset" class="mb-0">{{ __('Record Set Id') }} <span
                                                    class="color-action">*</span></label>
                                        <input type="text" name="recordset" id="recordset" class="form-control"
                                               required>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit"
                                                class="btn btn-primary pl-4 pr-4">{{ __('UPLOAD') }}</button>
                                        {!! Honeypot::generate('formuser', 'formtime') !!}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="import card">
                        <div class="card-header" id="dwc-url-import">
                            <h5 class="mb-0">
                                <button class="import btn btn-link collapsed" data-toggle="collapse"
                                        data-target="#dwc-uri" aria-expanded="false"
                                        aria-controls="dwc-uri">
                                    {{ __('Import Darwin Core Url') }}
                                </button>
                            </h5>
                        </div>
                        <div id="dwc-uri" class="collapse" aria-labelledby="dwc-url-import"
                             data-parent="#import-accordion">
                            <div class="card-body">
                                <form action="{{ route('admin.imports.dwcuri', ['projectId' => $project->id]) }}"
                                      method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
                                    {!! csrf_field() !!}
                                    <div class="mt-2 mb-4">
                                        <a href="#" class="btn btn-outline-primary collapsed preventDefault"
                                                data-toggle="collapse" data-target="#dwc-uri-instruction"
                                                aria-expanded="true"
                                                aria-controls="dwc-uri-instruction">{{ __('Instructions') }}</a>
                                        <span id="dwc-uri-instruction" class="collapse">
                                            {{ __('Copy and paste a url link to the zip file. Only zipped Darwin Core files are accepted.') }}
                                            <a href="{{ Storage::url('public/darwin-core-example.zip') }}" class="link">{{ __('Download Example DWC File.') }}</a>
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label for="data-url" class="mb-0">{{ __('URL') }} <span
                                                    class="color-action">*</span></label>
                                        <input type="text" name="data-url" id="data-url" class="form-control" required>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit"
                                                class="btn btn-primary pl-4 pr-4">{{ __('UPLOAD') }}</button>
                                        {!! Honeypot::generate('formuser', 'formtime') !!}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer text-center">
                <button type="button" class="btn btn-outline-primary color-action align-self-center"
                        data-dismiss="modal">{{ __('EXIT') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->