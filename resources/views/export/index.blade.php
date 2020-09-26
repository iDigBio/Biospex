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
                    <h3 class="mb-5 mx-auto">{{ t('Select Export Destination') }}:</h3>
                    <div class="col-sm-10 mx-auto text-center">
                        <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
                                data-toggle="collapse"
                                data-target="#geolocate"
                                data-hover="tooltip" title="{{ t('Export to GeoLocate') }}"
                                aria-expanded="false" aria-controls="collapseGeoLocate"
                        >{{ t('geolocate') }}</button>
                        <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
                                data-toggle="collapse"
                                data-target="#people"
                                data-hover="tooltip" title="{{ t('Export to People Standardization') }}"
                                aria-expanded="false" aria-controls="collapsePeople"
                        >{{ t('People') }}</button>
                    </div>
                </div>
                <div class="row">
                    <div id="geolocate" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.geolocate-frm-select')
                    </div>
                </div>
                <div class="row">
                    <div id="people" data-parent="#accordion" class="col-sm-6 mx-auto text-center mt-5 collapse">
                        @include('export.partials.people-frm-select')
                    </div>
                </div>
                <div class="row">
                    <div id="exportResults" class="col-sm-12 text-center mt-5"></div>
                </div>
            </div>
        </div>
    </div>
    </div>

@endsection
