@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('RAPID Record') }}
@stop

@section('custom-style')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ t('Record') }} {{ $id }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="row">
                <div class="col-sm-10 mx-auto">
                    <table id="record" class="display" data-route="{{ route('front.data.get', ['id' => $id]) }}">
                        <thead>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
<script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            $("#record").DataTable({
                "ajax": $('#record').data('route')
            });
        });
    </script>

@endsection
