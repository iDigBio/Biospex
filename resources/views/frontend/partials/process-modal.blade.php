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
