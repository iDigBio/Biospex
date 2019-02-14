@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Edit') }} {{ $expedition->title }}
@stop

@section('custom-style')
    <style>
        .ui-jqgrid.ui-jqgrid-bootstrap > .ui-jqgrid-view {
            font-size: 1rem;
        }
        /*
        .ui-jqdialog {
            display: none;
            float: left;
            font-size: 13px;
            height: 100px;
            left: 30% !important;
            overflow: hidden;
            position: absolute;
            top: 30% !important;
            z-index: 200;
        }
        */
    </style>
@endsection

{{-- Content --}}
@section('content')
    @include('admin.expedition.partials.expedition-panel')
    <form id="gridForm" method="post"
          action="{{ route('admin.expeditions.update', [$expedition->project->id, $expedition->id]) }}"
          role="form" enctype="multipart/form-data">
        {!! method_field('put') !!}
        {!! csrf_field() !!}
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                    <div class="col-12">
                        <h2 class="text-center content-header mb-4">{{ __('Edit Expedition') }}</h2>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('Title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title', $expedition->title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description" class="col-form-label required">{{ __('Description') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description')) ? 'is-invalid' : '' }}"
                                   id="description" name="description"
                                   value="{{ old('description', $expedition->description) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-form-label required">{{ __('Keywords') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('keywords')) ? 'is-invalid' : '' }}"
                                   id="keywords" name="keywords" placeholder="{{ __('Separated by commas') }}"
                                   value="{{ old('keywords', $expedition->keywords) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('keywords') }}</span>
                        </div>

                        @if(in_array($expedition->project->workflow_id, Config::get('config.nfnWorkflows'), false))
                            <div class="form-group">
                                <label for="workflow" class="col-form-label">{{ __('NfN Workflow Id') }}:</label>
                                <input type="text" name="workflow" id="workflow"
                                       class="form-control {{ ($errors->has('workflow')) ? 'has-error' : '' }}"
                                       placeholder="{{ __('Enter Workflow Id after Expedition submitted to Notes From Nature') }}"
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
            <h3 class="mx-auto">{{ __('Subjects currently assigned to Expedition') }}
                <span id="max">
                                {{ __('(:count max. per Expedition)', ['count' => Config::get('config.expedition_size')]) }}
                            </span>:
                <span id="subject-count-html"></span></h3>

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

            <div class="form-group col-md-12 text-center">
                <button type="submit" class="btn btn-primary mr-4">{{ __('SUBMIT') }}</button>
                <a href="{{ URL::previous() }}" class="btn btn-primary mr-4">{{ __('CANCEL') }}</a>
            </div>
        </div>
    </form>
    @include('admin.partials.jqgrid-modal')
@endsection
