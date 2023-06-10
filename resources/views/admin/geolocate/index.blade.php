@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('GeoLocate') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('GeoLocate') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-12 m-auto">
            <div class="row">
                <div id="exportResults" class="col-sm-6 mx-auto text-center mt-5">
                    @include('admin.geolocate.partials.setup-form')
                </div>
            </div>
        </div>
    </div>
@endsection
