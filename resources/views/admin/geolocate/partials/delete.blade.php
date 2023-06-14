<div class="row">
    <div class="col-sm-10 mx-auto text-center">
        <button type="button" id="downloadProduct" class="btn btn-primary pl-4 pr-4"
                data-hover="tooltip"
                data-url="{{ route('admin.download.export', ['file' => $data['frmName']]) }}"
                title="{{ t('Download the Export File') }}">{{ t('Download Export') }}</button>
        <button type="button" id="deleteExport" class="btn btn-primary pl-4 pr-4"
                data-href=" {{ route('admin.export.delete', ['id' => $data['frmId']]) }}"
                data-hover="tooltip"
                data-method="delete"
                data-confirm="confirmation"
                title="{{ t('Delete Export File and Data') }}"
                data-title="{{ t('Delete Export File') }}?"
                data-content="{{t('This will permanently delete the export file and data.') }}">
            {{ t('Delete Export') }}</button>
    </div>
</div>
