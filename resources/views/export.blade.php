@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Export') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('RapidRecord Export') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="text-center">
        {{ t('This will consist of a export functions') }}
    </div>

@endsection