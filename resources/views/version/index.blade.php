@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Version') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Record Version') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-sm-3 mx-auto text-center my-4">
            <a href="{{ route('admin.version.create') }}" type="submit"
               class="btn btn-primary text-uppercase"><i class="fas fa-plus-circle"></i> {{ t('Create Version File') }}</a>
        </div>
    </div>
    <div class="row">
        <div class="col-10 m-auto">
            <div class="card white box-shadow py-5 my-5 p-sm-5">
                <table id="version" class="table table-striped table-bordered dt-responsive"
                       data-route="{{ route('admin.version.show') }}">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>User</th>
                        <th>File</th>
                        <th>Created</th>
                        <th>Download</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript"
            src="https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.4/b-colvis-1.6.4/sb-1.0.0/sp-1.2.0/datatables.min.js"></script>
    <script>
        $(function () {
            let $version = $('#version');
            let table = $version.DataTable( {
                "ajax": $version.data('route'),
                "order": [[ 3, "desc" ]],
                "columns": [
                    { data: "id" },
                    { data: "user" },
                    { data: "file" },
                    { data: "created" },
                    { data: "download" , render : function ( data, type, row, meta ) {
                            return '<button type="button" class="btn btn-primary pl-4 pr-4 downloadFile" \
                                    data-hover="tooltip" data-url="'+data+'" \
                                    title="Download Version File">Download</button>';
                        }},
                    ]
            } );

            /*
            $('#version tbody').on( 'click', 'button', function () {
                let data = table.row( $(this).parents('tr') ).data();
                alert(data[4])
                window.location.href = data[4];
            } );

             */
        });
    </script>

@endpush

