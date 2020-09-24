@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('RAPID Record') }}
@stop

@section('custom-style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.4/b-colvis-1.6.4/sb-1.0.0/sp-1.2.0/datatables.min.css"/>
@endsection

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ t('Record') }} {{ $record->_id }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-12 col-md-10 offset-md-1">
        <div class="card white box-shadow py-5 my-5 p-sm-5">
            <div class="row">
                <div class="col-sm-10 mx-auto mb-5">
                    {!! $record->present()->gbif_link !!}
                    {!! $record->present()->idigbio_link !!}
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10 mx-auto">
                    <table id="record" class="table table-striped table-bordered dt-responsive" data-route="{{ route('front.data.get', ['id' => $record->_id]) }}">
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
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.4/b-colvis-1.6.4/sb-1.0.0/sp-1.2.0/datatables.min.js"></script>
    <script>
        $(function(){
            let dt = $("#record").DataTable({
                "ajax": $('#record').data('route'),
                /*
                dom:  "<'row'<'col-sm-4'l><'col-sm-4'B><'col-sm-4'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    {
                        text: 'GEOLocate',
                        action: function ( e, dt, node, config ) {
                            console.log('here');
                            dt.ajax.url($('#record').data('route') + '/geolocate').load();
                        }
                    }
                ]
                 */
            });
        });
    </script>

@endsection
