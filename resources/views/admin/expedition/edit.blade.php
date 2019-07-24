@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.edit') }} {{ $expedition->title }}
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
    @include('admin.expedition.partials.expedition-panel')
    <form id="gridForm" method="post"
          action="{{ route('admin.expeditions.update', [$expedition->project->id, $expedition->id]) }}"
          role="form" enctype="multipart/form-data">
        {!! method_field('put') !!}
        @csrf
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4 text-uppercase">{{ __('pages.edit') }} {{ __('pages.expedition') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('pages.title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title', $expedition->title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-form-label required">{{ __('pages.description') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description')) ? 'is-invalid' : '' }}"
                                   id="description" name="description"
                                   value="{{ old('description', $expedition->description) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-form-label required">{{ __('pages.keywords') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('keywords')) ? 'is-invalid' : '' }}"
                                   id="keywords" name="keywords" placeholder="{{ __('pages.separated_by_commas') }}"
                                   value="{{ old('keywords', $expedition->keywords) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('keywords') }}</span>
                        </div>
                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4">
                                <div class="custom-file">
                                    <label for="logo" class="custom-file-label">{{ __('pages.logo') }}:</label>
                                    <input type="file"
                                           class="form-control custom-file-input {{ ($errors->has('logo')) ? 'is-invalid' : '' }}"
                                           name="logo" id="logo"
                                           accept="image/png, image/jpg">
                                    <span class="invalid-feedback">{{ $errors->first('logo') }}</span>
                                </div>
                            </div>
                            <input type="hidden" name="current_logo" value="{{ $expedition->logo_file_name }}">
                            <div class="form-group col-sm-6">
                                <img class="img-fluid" style="display: inline; width: 100px; height: 100px;"
                                     src="{{ $expedition->logo->url('medium') }}"/>
                            </div>
                        </div>

                        @if(in_array($expedition->project->workflow_id, Config::get('config.nfnWorkflows'), false))
                            <div class="form-group">
                                <label for="workflow" class="col-form-label">{{ __('pages.nfn_workflow_id') }}:</label>
                                <input type="text" name="workflow" id="workflow"
                                       class="form-control {{ ($errors->has('workflow')) ? 'has-error' : '' }}"
                                       placeholder="{{ __('pages.nfn_workflow_id_placeholder') }}"
                                       value="{{ old('workflow', $expedition->nfnWorkflow->workflow ?? '') }}">
                                <span class="invalid-feedback">{{ $errors->first('workflow') }}</span>
                            </div>
                            @if(isset($expedition->nfnWorkflow->workflow))
                                <input type="hidden" name="current_workflow"
                                       value="{{ old('workflow', $expedition->nfnWorkflow->workflow ?? '') }}">
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mx-auto">{{ __('pages.subjects_assigned') }}
                <span id="max">
                                {{ __('pages.subjects_assigned_max', ['count' => Config::get('config.expedition_size')]) }}
                            </span>:
                <span id="subject-count-html"></span></h3>

            <div class="col-md-12 d-flex">
                <div class="table-responsive mb-4" id="jqtable">
                    <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                    <div id="pager"></div>
                    <br/>
                    <input type="hidden" name="subject-ids" id="subject-ids">
                    <a href="#" id="savestate" class="mr-2">{{ __('pages.grid_save_state') }}</a>
                    <a href="#" id="loadstate" class="ml-2">{{ __('pages.grid_load_state') }}</a>
                </div>
            </div>
            @include('common.cancel-submit-buttons')
        </div>
    </form>
    @include('admin.partials.jqgrid-modal')
@endsection
