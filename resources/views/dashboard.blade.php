@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Welcome') }}
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
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Dashboard') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-12 d-flex">
            <div class="table-responsive" id="jqtable">
                <table class="table table-bordered jgrid" id="jqGrid"></table>
                <div id="pager"></div>
            </div>
        </div>
    </div>
    @include('partials.jqgrid-modal')
    </div>

@endsection