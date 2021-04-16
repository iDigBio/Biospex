@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Export') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Record Export') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-12 m-auto">
            <div id="accordion" class="card white box-shadow py-5 my-5 p-sm-5">
                <div class="row">
                    @include('export.partials.export-destination')
                </div>
                <div class="row">
                    <div id="geolocate" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.frm-select-geolocate')
                    </div>
                </div>
                <div class="row">
                    <div id="people" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.frm-select-people')
                    </div>
                </div>
                <div class="row">
                    <div id="taxonomic" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.frm-select-taxonomic')
                    </div>
                </div>
                <div class="row">
                    <div id="generic" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.frm-select-generic')
                    </div>
                </div>
                <div class="row">
                    <div id="product" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.frm-select-product')
                    </div>
                </div>
                <div class="row">
                    <div id="exportResults" class="col-sm-12 text-center mt-5"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
