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
                <div class="col-sm-6 m-auto">
                    <h3 class="mb-5">{{ t('Select the export type below.') }}</h3>
                    <form id="exportSelect" class="form-inline justify-content-between">
                        <div class="form-check-inline">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="exportType"
                                       data-url="{{ route('admin.export.geolocate') }}"
                                       value="geolocate">{{ t('GeoLocate') }}
                            </label>
                        </div>
                        <div class="form-check-inline disabled">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="exportType"
                                       disabled>{{ t('Taxonomic Name Standardization') }}
                            </label>
                        </div>
                        <div class="form-check-inline disabled">
                            <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="exportType"
                                       disabled>{{ t('Darwin Core Archive') }}
                            </label>
                        </div>
                    </form>
                </div>
                <div id="ajaxResult"></div>
            </div>
        </div>
    </div>
    </div>

@endsection