<div class="modal fade" id="processModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h2 class="modal-title">@lang('pages.processes')</h2>
            </div>
            <div class="modal-body">
                <div><h4>@lang('pages.process_title', ['type' => 'OCR'])</h4></div>
                <div id="ocrHtml">@lang('pages.processing_empty')</div>
            </div>
            <div class="modal-body">
                <div><h4>@lang('pages.process_title', ['type' => 'Export'])</h4></div>
                <div id="exportHtml">@lang('pages.processing_empty')</div>
            </div>
            <div class="modal-footer">
                <span class="text-danger pull-left">@lang('pages.process_warning')</span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
