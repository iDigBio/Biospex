@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Export') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ __('Rapid Export') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="text-center">
        {{ __('This will consist of a export functions') }}
    </div>

@endsection