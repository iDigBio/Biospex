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
    </style>
@endsection

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel', ['project' => $project])
    <form id="gridForm" method="post"
          action="{{ route('admin.expeditions.store', [$project->id]) }}"
          role="form" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4">{{ __('CREATE EXPEDITION') }}</h2>
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

                        <div class="form-group">
                            <label for="workflow" class="col-form-label">{{ __('NfN Workflow Id') }}:</label>
                            <input type="text" name="workflow" id="workflow"
                                   class="form-control {{ ($errors->has('workflow')) ? 'has-error' : '' }}"
                                   placeholder="{{ __('Enter Workflow Id after Expedition submitted to Notes From Nature') }}"
                                   value="{{ old('workflow') }}">
                            <span class="invalid-feedback">{{ $errors->first('workflow') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mx-auto">{{ __('Subjects currently assigned to Expedition') }}
                <span id="max">
                                {{ __('(:count max. per Expedition)', ['count' => Config::get('config.expedition_size')]) }}
                            </span>:
                <span id="subject-count-html">0</span></h3>

            <div class="col-md-12 d-flex">
                <div class="table-responsive" id="jqtable">
                    <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                    <div id="pager"></div>
                    <br/>
                    <input type="hidden" name="subject-ids" id="subject-ids">
                    <a href="#" id="savestate" class="mr-2">{{ __('Save Grid State') }}</a>
                    <a href="#" id="loadstate" class="ml-2">{{ __('Load Grid State') }}</a>
                </div>
            </div>
            @include('common.cancel-submit-buttons')
        </div>
    </form>
    @include('admin.partials.jqgrid-modal')
@endsection
