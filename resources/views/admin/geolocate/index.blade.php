@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('GeoLocate') }}
@stop

{{-- Content --}}
@section('content')
    @include('admin.expedition.partials.expedition-panel')
    <div class="col-12 col-md-10 offset-md-1">
        <div class="jumbotron box-shadow py-5 my-5 p-sm-5">
            <h2 class="text-center text-uppercase pt-4">{{ t('GeoLocate') }}</h2>
            <hr class="header mx-auto" style="width:300px;">
            <form action="{{ route('admin.geolocate.store', [$expedition->project_id, $expedition->id]) }}"
                  method="post" role="form" id="geolocateFrm">
                @csrf
                <input type="hidden" id="frmDataExists" name="frmDataExists" value="{{ !($frmData['data'] === null) }}">
                <input type="hidden" id="entries" name="entries"
                       value="{{ old('entries', isset($frmData['entries'])) ? $frmData['entries'] : 0 }}">
                @isset($frmData['data'])
                    @include('admin.geolocate.partials.delete')
                @endisset
                <div class="row">
                    <div class="col-sm-10 mx-auto text-center">
                        @include('admin.geolocate.partials.source-type')
                    </div>
                </div>
                <div class="row">
                    <div id="exportResults" class="col-sm-12 text-center m-auto mt-5"></div>
                </div>
            </form>
            <div class="row default mt-2" style="display: none">
                @include('admin.geolocate.partials.geolocate-field-select-default')
                @include('admin.geolocate.partials.header-field-select-default')
            </div>
        </div>
    </div>
@endsection
