<select id="productSelect" class="selectpicker form-select"
        data-url="{{ route('admin.export.show', ['product']) }}"
        data-live-search="true"
        data-actions-box="true"
        data-target="#product"
        title="{{ t('Product Data Forms') }}"
        data-header="{{ t('Select New or Saved Form') }}"
        data-width="250"
        data-style="btn-primary">
    <option value="" class="text-uppercase">{{ t('New') }}</option>
    @isset($forms['product'])
        @foreach($forms['product'] as $frm)
            <option value="{{ $frm->id }}">{{ $frm->present()->form_name_user }}</option>
        @endforeach
    @endisset
</select>
