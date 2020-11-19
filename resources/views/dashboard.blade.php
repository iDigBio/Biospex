@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Welcome') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Dashboard') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered" id="jqGridTable"></table>
        </div>
    </div>

@endsection