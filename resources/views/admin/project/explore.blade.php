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
        <h3 class="mx-auto">{{ __('Subjects currently assigned to Expeditions') }}: <span
                    id="subjectCount">{{ $subjectAssignedCount }}</span></h3>
        <div class="col-md-12 d-flex">
            <div class="table-responsive" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGridExplore"></table>
                <div id="pager"></div>
                <br/>
                <input type="hidden" name="subject-ids" id="subject-ids">
                <a href="#" id="savestate" class="mr-2">{{ __('Save Grid State') }}</a>
                <a href="#" id="loadstate" class="ml-2">{{ __('Load Grid State') }}</a>
            </div>
        </div>
    </div>
    @include('admin.partials.jqgrid-modal')
@endsection
