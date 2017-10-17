<div class="modal fade" id="processModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h2 class="modal-title">@lang('pages.processes')</h2>
            </div>
            <div class="modal-body">
                <div><h4>@lang('pages.process_title', ['type' => 'OCR'])</h4></div>
                <div id="ocrHtml">@lang('pages.retrieve_process', ['type' => 'OCR'])</div>
            </div>
            <div class="modal-body">
                <div><h4>@lang('pages.process_title', ['type' => 'Export'])</h4></div>
                <div id="exportHtml">@lang('pages.retrieve_process', ['type' => 'Export'])</div>
            </div>
            <div class="modal-footer">
                <span class="text-danger pull-left">@lang('pages.process_warning')</span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script src="{{ asset('/js/socket.io.js') }}"></script>
<script>
    var socket = io('{{ config('config.app_nodejs_url') }}');
    <?php
    $uuids = Session::get('user-groups');
    ?>
    socket.on("{!! config('config.poll_ocr_channel') !!}:app.polling", function (message) {
        var uuids = {!! json_encode($uuids) !!};
        var ocrHtml = '';
        var data = message.data;

        if($.isArray(data)) {
            $.each(data, function (index) {
                if ($.inArray(data[index].groupUuid, uuids) == -1) {
                    return true;
                }
                ocrHtml += data[index].message;
            });
        } else {
            ocrHtml = data;
        }

        $('#ocrHtml').html(ocrHtml);
    });

    socket.on("{!! config('config.poll_export_channel') !!}:app.polling", function (message) {
        var uuids = {!! json_encode($uuids) !!};
        var exportHtml = '';
        var data = message.data;

        if($.isArray(data)) {
            $.each(data, function (index) {
                if ($.inArray(data[index].groupUuid, uuids) == -1) {
                    return true;
                }
                exportHtml += data[index].message;
            });
        } else {
            exportHtml = data;
        }

        $('#exportHtml').html(exportHtml);
    });

</script>