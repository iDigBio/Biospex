<div class="modal fade" id="processModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h2 class="modal-title">@lang('pages.processes')</h2>
            </div>
            <div class="modal-body">
                @if (Auth::check())
                    <div><h4>@lang('pages.process_title', ['type' => 'OCR'])</h4></div>
                    <div id="processHtml">@lang('pages.retrieve_process', ['type' => 'OCR'])</div>
                @endif
            </div>
            <div class="modal-body">
                @if (Auth::check())
                    <div><h4>@lang('pages.process_title', ['type' => 'Import'])</h4></div>
                    <div id="importHtml">@lang('pages.no_processes')</div>
                @endif
            </div>
            <div class="modal-footer">
                <span class="text-danger pull-left">@lang('pages.process_warning')</span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script src="/node/socket.io/socket.io.js"></script>
@if (Auth::check())
    <script>
        var socket = io('http://{{ Config::get('config.app_domain') }}/node');
        <?php
        $groups = Auth::getUser()->groups()->get();
        $uuids = $groups->map(function ($item, $key) {
            return $item['uuid'];
        });
        ?>
        socket.on("{!! Config::get('config.ocr_poll_channel') !!}:app.polling", function (message) {
            var html = '{!! trans('pages.no_processes') !!}';
            var uuids = {!! json_encode($uuids) !!};

            if (jQuery.isEmptyObject(message)) {
                $("#processHtml").text(html);

                return;
            }

            var processHtml = '';
            var data = message.data;

            $.each(data, function (index){
                if( $.inArray(data[index].groupUuid, uuids) == -1 ) {
                    return true;
                }
                processHtml += '<div class="processes"><span class="title">' + data[index].projectTitle + '</span><br />' +
                        'Ocr Batch #' + data[index].batchId + ' - ';
                processHtml += data[index].groupSubjectRemaining + " out of " + data[index].groupSubjectCount + " remaining to be processed";
                if (data[index].totalSubjectsAhead > 0) {
                    processHtml += '<br />' + data[index].totalSubjectsAhead + ' subjects being processed before this batch begins';
                }
                processHtml += '</div>';
            });

            $("#processHtml").html(processHtml.length > 0 ? processHtml : html);
        });

    </script>
@endif