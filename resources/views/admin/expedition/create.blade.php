@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Create Expedition') }}
@stop

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel', ['project' => $project])
    <form id="gridForm" method="post"
          action="{{ route('admin.expeditions.store', [$project->id]) }}"
          role="form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="subject-ids" id="subject-ids">
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4 text-uppercase">{{ t('Create Expedition') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ t('Title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-form-label required">{{ t('Description') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description')) ? 'is-invalid' : '' }}"
                                   id="description" name="description"
                                   value="{{ old('description') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-form-label required">{{ t('Keywords') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('keywords')) ? 'is-invalid' : '' }}"
                                   id="keywords" name="keywords" placeholder="{{ t('Separated by commas') }}"
                                   value="{{ old('keywords') }}" required>
                            <span class="invalid-feedback">{{ $errors->first('keywords') }}</span>
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4">
                                <div class="custom-file">
                                    <label for="logo" class="custom-file-label">{{ t('Logo') }}:</label>
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
                            <label for="workflow_id" class="col-form-label col-12 required">{{ t('Workflows') }}
                                :</label>
                            <select name="workflow_id" id="workflow_id"
                                    class="form-control custom-select col-sm-5 {{ ($errors->has('workflow_id')) ? 'is-invalid' : '' }}"
                                    required>
                                @foreach($workflowOptions as $key => $name)
                                    <option value="{{ $key }}" {{ $key == old('workflow_id') ? ' selected=selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback">{{ $errors->first('workflow_id') }}</span>
                        </div>
                        <input type="hidden" name="locked" value="1">
                        <!--
                        <div class="form-group">
                            <input type="checkbox" id="assign_all" name="assign_all" value="true">
                            <label for="assign_all" class="col-form-label">{{ t('Automatically Assign All Unassigned Subjects From Project') }}</label>
                            <span class="invalid-feedback">{{ $errors->first('assign_all') }}</span>
                        </div>
                        -->


                    </div>
                </div>
            </div>
            @include('common.cancel-submit-buttons')
        </div>
    </form>
    <div class="row">
        <h3 class="mx-auto">{{ t('Subjects currently assigned') }}
            <span id="max">{{ t('(%s max. per Expedition)', Config::get('config.expedition_size')) }}</span>:
            <span id="subject-count-html">0</span></h3>
        <div class="col-md-12">
            <table class="table table-bordered" id="jqGridTable"></table>
        </div>
    </div>
@endsection
