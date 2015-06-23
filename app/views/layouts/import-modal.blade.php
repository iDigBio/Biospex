<!-- Modal HTML -->
<div id="recordsetModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">@lang('pages.recordset_modal_header')</h4>
            </div>
            <div class="modal-body">
                @lang('modal.recordset_modal_message')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('pages.close')</button>
            </div>
        </div>
    </div>
</div>

<div id="dataModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">@lang('pages.data_modal_header')</h4>
            </div>
            <div class="modal-body">
                {{ trans('pages.add_data_desc', ['link' => link_to_asset('darwin-core-example.zip', "Darwin Core Example")]) }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('pages.close')</button>
            </div>
        </div>
    </div>
</div>

<div id="transcriptionModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">@lang('pages.transcription_modal_header')</h4>
            </div>
            <div class="modal-body">
                <p>{{ trans('pages.upload_nfn_desc') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('pages.close')</button>
            </div>
        </div>
    </div>
</div>
