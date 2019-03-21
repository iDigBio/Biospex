@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }} @lang('pages.project_stats')
@stop

{{-- Content --}}
@section('content')
    @include('frontend.statistics.partials.project-info')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading panel-title">
                    {{ trans('pages.transcriber_summary') }}
                    <i class="fa fa-expand pull-right"></i>
                </div>
                <div id="transcribers" class="panel-body">
                    <table class="table-responsive table-sort">
                        <thead>
                        <tr>
                            <th>User</th>
                            <th>Expeditions</th>
                            <th>Transcriptions</th>
                            <th>Last Date</th>
                        </tr>
                        </thead>
                        @each('frontend.statistics.partials.transcriber', $transcribers, 'transcriber')
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div id="chartdiv" style="width: 100%; height: 600px; color: #000000; font-size: 0.8em"></div>
        </div>
    </div>
@stop
@section('custom-script')
    @if($transcribers->isNotEmpty())
        <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $('#transcribers-tbl').DataTable();
        </script>
    @endif
    @if(isset($transcriptions))
        <script>
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
    @endif
@endsection