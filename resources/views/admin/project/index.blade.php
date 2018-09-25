@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Projects') }}
@stop

{{-- Content --}}
@section('content')
    This is my content
@endsection

@section('footer')
    @include('common.footer')
    @include('common.contributors')
@endsection