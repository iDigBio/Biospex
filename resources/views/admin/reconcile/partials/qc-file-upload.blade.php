<div class="col-md-10 mx-auto mt-3 mb-3">
    <p class="text-justify">
        {{ t('This form provides a means to upload a quality controlled file generated from the Reconcile or Expert
             Review process and edited outside Biospex. The file must be in CSV format and columns must match those in
             the Reconciled or Expert Review Reconciled CSV files.') }}
    </p>
    <form id="someForm" class="modal-form" method="post" action="" role="form">
        @csrf
        <div class="form-row justify-content-center">
            <div class="form-group col-sm-10">
                <div class="custom-file">
                    <label for="file" class="custom-file-label">{{ t('Upload CSV File') }}</label>
                    <input type="file" class="form-control custom-file-input mb-4 {{ ($errors->has('file')) ? 'is-invalid' : '' }}" name="file" accept=".csv">
                </div>
            </div>
        </div>
        <div class="form-row justify-content-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
        <div class="feedback text-center mt-3"></div>
    </form>
</div>