@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Explore Subjects') }}
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
        <h3 class="mx-auto">{{ t('Subjects currently assigned') }}: <span
                    id="subjectCount">{{ $subjectAssignedCount }}</span></h3>
        <div class="col-md-12 d-flex">
            <div class="table-responsive mb-4" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGridExplore"></table>
                <div id="pager"></div>
                <br/>
                <input type="hidden" name="subject-ids" id="subject-ids">
            </div>
        </div>
    </div>
    @include('admin.partials.jqgrid-modal')
@endsection
