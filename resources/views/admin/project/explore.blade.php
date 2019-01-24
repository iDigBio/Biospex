@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Explore Subjects') }}
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
    @include('admin.project.partials.project-panel')
    <div class="row">
        <h3 class="mx-auto">{{ trans_choice('Subjects currently assigned to Expedition|Subjects currently assigned to Expeditions', $subjectAssignedCount) }}: <span
                    id="subjectCount">{{ $subjectAssignedCount }}</span></h3>
        <div class="col-md-12 d-flex">
            <div class="table-responsive" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGridExplore"></table>
                <div id="pager"></div>
                <br/>
                <input type="hidden" name="subject-ids" id="subject-ids">
                <button id="savestate" class="btn btn-default">{{ __('Save Grid State') }}</button>
                <button id="loadstate" class="btn btn-default">{{ __('Load Grid State') }}</button>
            </div>
        </div>
    </div>
    @include('admin.partials.jqgrid-modal')
@endsection
