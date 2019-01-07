@extends('backend.layouts.app')

@section('htmlheader_title')
    Log
@endsection

@section('contentheader_title', 'View Log')

@section('styles')
    <link rel="stylesheet"
          href="https://cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.css">
    <style>
        h1 {
            font-size: 1.5em;
            margin-top: 0;
        }

        .stack {
            font-size: 0.85em;
        }

        .date {
            min-width: 75px;
        }

        .text {
            word-break: break-all;
        }

        a.llv-active {
            z-index: 2;
            background-color: #f5f5f5;
            border-color: #777;
        }
    </style>
@endsection

@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                <div class="list-group">
                    @foreach($files as $file)
                        <a href="?l={{ base64_encode($file) }}"
                           class="list-group-item @if ($current_file == $file) llv-active @endif">
                            {{$file}}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 table-container">
                @if ($logs === null)
                    <div>
                        Log file >50M, please download it.
                    </div>
                @else
                    <table id="table-log" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Level</th>
                            <th>Context</th>
                            <th>Date</th>
                            <th>Content</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($logs as $key => $log)
                            <tr data-display="stack{{{$key}}}">
                                <td class="text-{{{$log['level_class']}}}"><span class="glyphicon glyphicon-{{{$log['level_img']}}}-sign"
                                                                                 aria-hidden="true"></span> &nbsp;{{$log['level']}}</td>
                                <td class="text">{{$log['context']}}</td>
                                <td class="date">{{{$log['date']}}}</td>
                                <td class="text">
                                    @if ($log['stack']) <a class="pull-right expand btn btn-default btn-xs"
                                                           data-display="stack{{{$key}}}"><span
                                                class="glyphicon glyphicon-search"></span></a>@endif
                                    {{{$log['text']}}}
                                    @if (isset($log['in_file'])) <br/>{{{$log['in_file']}}}@endif
                                    @if ($log['stack'])
                                        <div class="stack" id="stack{{{$key}}}"
                                             style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}
                                        </div>@endif
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                @endif
                <div>
                    @if($current_file)
                        <a href="?dl={{ base64_encode($current_file) }}"><span class="glyphicon glyphicon-download-alt"></span>
                            Download file</a>
                        -
                        <a id="delete-log" href="?del={{ base64_encode($current_file) }}"><span
                                    class="glyphicon glyphicon-trash"></span> Delete file</a>
                        @if(count($files) > 1)
                            -
                            <a id="delete-all-log" href="?delall=true"><span class="glyphicon glyphicon-trash"></span> Delete all files</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/9dcbecd42ad/integration/bootstrap/3/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('.table-container tr').on('click', function () {
                $('#' + $(this).data('display')).toggle();
            });
            $('#table-log').DataTable({
                "order": [1, 'desc'],
                "stateSave": true,
                "stateSaveCallback": function (settings, data) {
                    window.localStorage.setItem("datatable", JSON.stringify(data));
                },
                "stateLoadCallback": function (settings) {
                    var data = JSON.parse(window.localStorage.getItem("datatable"));
                    if (data) data.start = 0;
                    return data;
                }
            });
            $('#delete-log, #delete-all-log').click(function () {
                return confirm('Are you sure?');
            });
        });
    </script>
@endsection