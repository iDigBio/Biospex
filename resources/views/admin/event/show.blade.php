@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $event->title }}
@stop

@push('styles')
    <link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"
          rel="stylesheet"/>
@endpush

{{-- Content --}}
@section('content')
    @include('admin.event.partials.event-panel')
    @include('admin.event.partials.team-table')
@stop

@push('scripts')
    @if($event->teams->isNotEmpty())
        <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $('#teams-tbl').DataTable();
        </script>
    @endif
@endpush