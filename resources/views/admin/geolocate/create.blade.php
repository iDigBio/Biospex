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
            <div class="row">
                <div id="exportResults" class="col-sm-12 text-center m-auto mt-5">
                    @include('admin.geolocate.partials.form')
                </div>
            </div>
        </div>
    </div>
@endsection
