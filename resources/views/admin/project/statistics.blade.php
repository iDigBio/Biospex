@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }} {{ __('Statistics') }}
@stop

@section('custom-style')
    <link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"
          rel="stylesheet"/>
@endsection
@section('content')
    @include('admin.project.partials.project-panel')
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card-transcribe px-4 box-shadow h-100">
                <h3 class="text-center pt-4">{{ __('Transcriber Summary') }}</h3>
                <div class="color-action text-center">{{ __('Use shift + click to multi-sort') }}</div>
                <hr>
                <div class="row card-body">
                    <table id="transcribers-tbl" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%; font-size: .8rem">
                        <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Expeditions') }}</th>
                            <th>{{ __('Transcriptions') }}</th>
                            <th>{{ __('Last Date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @each('admin.project.partials.transcribers', $transcribers, 'transcriber')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card-transcribe px-4 box-shadow h-100">
                <h3 class="text-center pt-4">{{ __('Transcriptions') }}</h3>
                <hr>
                <div class="row card-body">
                    <div id="chartdiv" style="width: 100%; height: 600px; color: #000000; font-size: 0.8em"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('custom-script')
    <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $('#transcribers-tbl').DataTable({
            "pagingType": "full"
        });

        var chart = am4core.createFromConfig(
            {
                "xAxes": [{
                    "type": "CategoryAxis",
                    "title": {
                        "text": "Transcriptions"
                    },
                    "dataFields": {
                        "category": "transcriptions"
                    },
                    "tooltip": {
                        "background": {
                            "fill": "#07BEB8",
                            "strokeWidth": 0,
                            "cornerRadius": 3,
                            "pointerLength": 0
                        },
                        "dy": 5
                    }
                }],
                "yAxes": [{
                    "type": "ValueAxis",
                    "title": {
                        "text": "Number of Transcribers"
                    },
                    "tooltip": {
                        "disabled": true
                    },
                    "calculateTotals": true
                }],
                "cursor": {
                    "type": "XYCursor",
                    "lineX": {
                        "stroke": "#8F3985",
                        "strokeWidth": 4,
                        "strokeOpacity": 0.2,
                        "strokeDasharray": ""
                    },
                    "lineY": {
                        "disabled": true
                    }
                },
                "scrollbarX": {
                    "type": "Scrollbar"
                },
                "series": [{
                    "type": "ColumnSeries",
                    "dataFields": {
                        "valueY": "transcribers",
                        "categoryX": "transcriptions"
                    },
                    "tooltipHTML": "<span style='color:#000000;'>{valueY.value} Transcribers: {categoryX} Transcriptions</span>",
                    "tooltip": {
                        "background": {
                            "fill": "#FFF",
                            "strokeWidth": 1
                        },
                        "getStrokeFromObject": true,
                        "getFillFromObject": false
                    },
                    "fillOpacity": 0.8,
                    "strokeWidth": 0,
                    "stacked": true
                }],
                "data": {!! $transcriptions !!}
            }, "chartdiv", am4charts.XYChart);
    </script>
@endsection