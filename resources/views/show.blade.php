@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('RAPID Record') }}
@stop

@push('styles')
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.4/b-colvis-1.6.4/sb-1.0.0/sp-1.2.0/datatables.min.css"/>
@endpush

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
                    <table id="record" class="table table-striped table-bordered dt-responsive"
                           data-id="{{ $record->_id }}"
                           data-route="{{ route('front.data.get', $dataVars) }}">
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

@push('scripts')
    <script type="text/javascript"
            src="https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.4/b-colvis-1.6.4/sb-1.0.0/sp-1.2.0/datatables.min.js"></script>
    <script>
        $(function () {
            let table = $("#record").DataTable({
                "iDisplayLength": 50,
                "ajax": $('#record').data('route'),

                dom: "<'row'<'col-sm-4'l><'col-sm-4'B><'col-sm-4'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    {
                        text: 'GEOLocate',
                        action: function (e, dt, node, config) {
                            window.location.replace('/record/geolocate/' + $('#record').data('id'));
                            /*
                            table.ajax.url($('#record').data('show') + '/data/geolocate').load(function (result) {
                                console.log(result);
                                table.clear().rows.add([
                                    result.data
                                ]).draw();
                            });
                             */
                        }
                    }
                ],
                "order": []

            });
        });
    </script>

@endpush
