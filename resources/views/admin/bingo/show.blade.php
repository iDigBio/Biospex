@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $bingo->title }}
@stop

@push('styles')
    <link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"
          rel="stylesheet"/>
@endpush

{{-- Content --}}
@section('content')
    @include('admin.bingo.partials.bingo-panel')
    @include('admin.bingo.partials.words-table')
@stop

@push('scripts')
    <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $('#words-tbl').DataTable();
    </script>
@endpush