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
            <div class="col-sm-4 mx-auto text-center my-4">
                <a href="{{ $route }}" type="submit"
                   class="btn btn-primary text-uppercase">{!! $isForm ? '' : '<i class="fas fa-plus-circle"></i>' !!} {{ $isForm ? t('View Export Form') : t('Create Export Form') }}</a>
            </div>
            <div class="row">
                <div class="col-sm-10 mx-auto">
                    Will use this space for any information or stats from GeoLocate
                </div>
            </div>
        </div>
    </div>
@endsection