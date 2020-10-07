@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }} {{ t('Statistics') }}
@stop

@section('custom-style')
    <link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"
          rel="stylesheet"/>
@endsection
@section('content')
    @include('admin.project.partials.project-panel')
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card white px-4 box-shadow h-100">
                <h3 class="text-center pt-4">{{ t('Participant Summary') }}</h3>
                <hr>
                @if($transcribers->isEmpty())
                    <p class="text-center">{{ t('No digitizations exist.') }}</p>
                @else
                    <div class="color-action text-center">{{ t('Use shift + click to multi-sort') }}</div>
                    <div class="row card-body">
                        <table id="transcribers-tbl" class="table table-striped table-bordered dt-responsive nowrap"
                               style="width:100%; font-size: .8rem">
                            <thead>
                            <tr>
                                <th>{{ t('User') }}</th>
                                <th>{{ t('Expeditions') }}</th>
                                <th>{{ t('Digitizations') }}</th>
                                <th>{{ t('Last Date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @each('admin.project.partials.transcribers', $transcribers, 'transcriber')
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card white px-4 box-shadow h-100">
                <h3 class="text-center pt-4">{{ t('Digitizations') }}</h3>
                <hr>
                @if(isset($transcriptions))
                    <div class="row card-body">
                        <div id="statDiv" style="width: 100%; height: 600px; color: #000000; font-size: 0.8em"></div>
                    </div>
                @else
                    <p class="text-center">{{ t('No digitizations exist.') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('custom-script')
    @if($transcribers->isNotEmpty())
        <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $('#transcribers-tbl').DataTable();
        </script>
    @endif
    @if(isset($transcriptions))
        <script src="//www.amcharts.com/lib/4/core.js"></script>
        <script src="//www.amcharts.com/lib/4/charts.js"></script>
        <script src="{{ asset('js/amChartStat.min.js')}}"></script>
    @endif
@endsection