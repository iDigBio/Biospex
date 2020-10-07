@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $expedition->title }}
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
    @include('admin.expedition.partials.expedition-panel')
    <div class="row">
        <h3 class="mx-auto">{{ t('Subjects currently assigned') }}:
            {{ $expedition->stat->local_subject_count }}
        </h3>

        <div class="col-md-12 d-flex">
            <div class="table-responsive" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGridExpedition"></table>
                <div id="pager"></div>
                <br/>
                <input type="hidden" name="subject-ids" id="subject-ids">
            </div>
        </div>
    </div>
    @include('admin.partials.jqgrid-modal')
@endsection

