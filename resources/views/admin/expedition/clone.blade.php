@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Clone') }} {{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel', ['project' => $expedition->project])
    <form id="gridForm" method="post"
          action="{{ route('admin.expeditions.store', [$expedition->project->id, $expedition->id]) }}"
          role="form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="project_id" value="{{ $expedition->project->id }}">
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4 text-uppercase">{{ t('Clone Expedition') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ t('Title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title', $expedition->title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-form-label required">{{ t('Description') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description')) ? 'is-invalid' : '' }}"
                                   id="description" name="description"
                                   value="{{ old('description', $expedition->description) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-form-label required">{{ t('Keywords') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('keywords')) ? 'is-invalid' : '' }}"
                                   id="keywords" name="keywords" placeholder="{{ t('Separated by commas') }}"
                                   value="{{ old('keywords', $expedition->keywords) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('keywords') }}</span>
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4">
                                <div class="custom-file">
                                    <label for="logo" class="custom-file-label">{{ t('Logo') }}:</label>
                                    <input type="file"
                                           class="form-control custom-file-input {{ ($errors->has('logo')) ? 'is-invalid' : '' }}"
                                           name="logo" id="logo"
                                           accept="image/svg+xml, image/png, image/jpg">
                                    <span class="invalid-feedback">{{ $errors->first('logo') }}</span>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <img class="img-fluid" style="display: inline; width: 100px; height: 100px;"
                                     src="{{ GeneralHelper::expeditionDefaultLogo() }}"/>
                            </div>
                        </div>

                        @if(in_array($expedition->project->workflow_id, Config::get('config.nfnWorkflows'), false))
                            <div class="form-group">
                                <label for="panoptes_workflow_id" class="col-form-label">{{ t('Zooniverse Workflow Id') }}:</label>
                                <input type="text" name="panoptes_workflow_id" id="panoptes_workflow_id"
                                       class="form-control {{ ($errors->has('panoptes_workflow_id')) ? 'has-error' : '' }}"
                                       placeholder="{{ t('Enter Workflow Id after Expedition submitted to Zooniverse') }}"
                                       value="{{ old('panoptes_workflow_id') }}">
                                <span class="invalid-feedback">{{ $errors->first('panoptes_workflow_id') }}</span>
                            </div>
                            @if(isset($expedition->panoptesProject->panoptes_workflow_id))
                                <input type="hidden" name="current_panoptes_workflow_id"
                                       value="{{ old('panoptes_workflow_id') }}">
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mx-auto">{{ t('Subjects currently assigned') }}
                <span id="max">
                                {{ t('(%s max. per Expedition)', Config::get('config.expedition_size')) }}
                            </span>:
                <span id="subject-count-html">0</span></h3>

            <div class="col-md-12">
                <table class="table table-bordered" id="jqGridTable"></table>
                <br/>
                <input type="hidden" name="subject-ids" id="subject-ids">
            </div>
            @include('common.cancel-submit-buttons')
        </div>
    </form>
    @include('admin.partials.jqgrid-modal')
@endsection