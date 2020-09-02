@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Create Expedition') }}
@stop

@section('custom-style')
    <style>
        .ui-jqgrid.ui-jqgrid-bootstrap > .ui-jqgrid-view {
            font-size: 1rem;
        }
        #searchmodfbox_jqGridExpedition {
            top:auto;
        }
    </style>
@endsection

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel', ['project' => $project])
    <form id="gridForm" method="post"
          action="{{ route('admin.expeditions.store', [$project->id]) }}"
          role="form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4 text-uppercase">{{ __('Create Expedition') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('Title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-form-label required">{{ __('Description') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description')) ? 'is-invalid' : '' }}"
                                   id="description" name="description"
                                   value="{{ old('description') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-form-label required">{{ __('Keywords') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('keywords')) ? 'is-invalid' : '' }}"
                                   id="keywords" name="keywords" placeholder="{{ __('Separated by commas') }}"
                                   value="{{ old('keywords') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('keywords') }}</span>
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4">
                                <div class="custom-file">
                                    <label for="logo" class="custom-file-label">{{ __('Logo') }}:</label>
                                    <input type="file"
                                           class="form-control custom-file-input {{ ($errors->has('logo')) ? 'is-invalid' : '' }}"
                                           name="logo" id="logo"
                                           accept="image/png,image/jpg">
                                    <span class="invalid-feedback">{{ $errors->first('logo') }}</span>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <img class="img-fluid" style="display: inline; width: 100px; height: 100px;" src="{{ GeneralHelper::expeditionDefaultLogo() }}"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="panoptes_workflow_id" class="col-form-label">{{ __('Zooniverse Workflow Id') }}:</label>
                            <input type="text" name="panoptes_workflow_id" id="panoptes_workflow_id"
                                   class="form-control {{ ($errors->has('panoptes_workflow_id')) ? 'has-error' : '' }}"
                                   placeholder="{{ __('Enter Workflow Id after Expedition submitted to Zooniverse') }}"
                                   value="{{ old('panoptes_workflow_id') }}">
                            <span class="invalid-feedback">{{ $errors->first('panoptes_workflow_id') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mx-auto">{{ __('Subjects currently assigned') }}
                <span id="max">
                                {{ t('(%s max. per Expedition)', Config::get('config.expedition_size')] }}
                            </span>:
                <span id="subject-count-html">0</span></h3>

            <div class="col-md-12 d-flex">
                <div class="table-responsive mb-4" id="jqtable">
                    <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                    <div id="pager"></div>
                    <br/>
                    <input type="hidden" name="subject-ids" id="subject-ids">
                </div>
            </div>
            @include('common.cancel-submit-buttons')
        </div>
    </form>
    @include('admin.partials.jqgrid-modal')
@endsection
