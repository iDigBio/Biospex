<hr class="header mx-auto" style="width:500px;">
<form action="{{ route('admin.export.create', ['taxonomic']) }}"
      method="post" role="form" class="exportFrm">
    @csrf
    <input type="hidden" name="exportDestination" value="taxonomic">
    <div class="row">
        <div class="col-sm-10 mx-auto text-center mb-5">
            @include('export.partials.export-type')
        </div>
    </div>
    <div class="row">
        <button type="submit" id="taxonomicSubmit"
                class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Submit') }}</button>
    </div>
</form>