@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.welcome') }}
@stop

@section('custom-style')
    <!--
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.15.5/plugins/css/ui.multiselect.min.css">
    -->
    <style>
        .ui-jqgrid.ui-jqgrid-bootstrap > .ui-jqgrid-view {
            font-size: 1rem;
        }
    </style>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ __('RapidRecords Dashboard') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-12 d-flex">
            <div class="table-responsive" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGrid"></table>
                <div id="pager"></div>
                <br/>
                <a href="#" id="savestate" class="mr-2">{{ __('pages.grid_save_state') }}</a>
                <a href="#" id="loadstate" class="ml-2">{{ __('pages.grid_load_state') }}</a>
            </div>
        </div>
    </div>
    @include('partials.jqgrid-modal')
    </div>

@endsection