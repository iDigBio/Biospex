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
            <div class="card white box-shadow py-5 my-5 p-sm-5">
                <h3 class="mb-5 mx-auto">{{ t('Select the export type below') }}:</h3>
                <div class="col-sm-10 mx-auto text-center">
                    <button type="button" class="export-btn btn btn-primary pl-4 pr-4 text-uppercase"
                            data-url="{{ route('admin.export.geolocate') }}"
                            data-hover="tooltip" title="{{ t('Export to GeoLocate') }}"
                    >{{ t('GeoLocate') }}</button>
                    <button type="button" disabled class="export-btn btn btn-primary pl-4 pr-4 text-uppercase"
                            data-url="#"
                            data-hover="tooltip" title="{{ t('Export to Taxonomic Name Standardization') }}"
                    >{{ t('Taxonomic Name Standardization') }}</button>
                    <button type="button" disabled class="export-btn btn btn-primary pl-4 pr-4 text-uppercase"
                            data-url="#"
                            data-hover="tooltip" title="{{ t('Export to Darwin Core Archive') }}"
                            data-value="{{ t('Darwin Core Archive') }}"
                    >{{ t('Darwin Core Archive') }}</button>
                </div>
                <div id="export-results"></div>
            </div>
        </div>
    </div>
    </div>

@endsection