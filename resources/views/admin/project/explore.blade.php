@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.explore') }} {{ __('pages.subjects') }}
@stop

@section('custom-style')
    <style>
        .ui-jqgrid.ui-jqgrid-bootstrap > .ui-jqgrid-view {
            font-size: 1rem;
        }
        #searchmodfbox_jqGridExplore{
            top:auto;
        }
    </style>
@endsection

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel')
    <div class="row">
        <h3 class="mx-auto">{{ __('pages.subjects_assigned') }}: <span
                    id="subjectCount">{{ $subjectAssignedCount }}</span></h3>
        <div class="col-md-12 d-flex">
            <div class="table-responsive mb-4" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGridExplore"></table>
                <div id="pager"></div>
                <br/>
                <input type="hidden" name="subject-ids" id="subject-ids">
                <a href="#" id="savestate" class="mr-2">{{ __('pages.grid_save_state') }}</a>
                <a href="#" id="loadstate" class="ml-2">{{ __('pages.grid_load_state') }}</a>
            </div>
        </div>
    </div>
    @include('admin.partials.jqgrid-modal')
@endsection