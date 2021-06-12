<form action="{{ route('admin.product.create') }}" method="post" role="form" class="productFrm m-auto">
    @csrf
    <div class="row">
        <div class="col">
            <input type="text" class="form-control {{ ($errors->has('key-text')) ? 'is-invalid' : '' }}"
                   id="key-text" name="key-text"
                   placeholder="{{ t('Provider Key') }}"
                   value="{{ old('key-text') }}">
        </div>
        <div class="col">
            <input type="text" class="form-control {{ ($errors->has('name-text')) ? 'is-invalid' : '' }}"
                   id="name-text" name="name-text"
                   placeholder="{{ t('Provider Name') }}"
                   value="{{ old('provider') }}">
        </div>
    </div>
    <div class="row mt-3">
        <p class="m-auto">Or</p>
    </div>
    <div class="row mt-3">
        <select class="selectpicker" name="key-select"
                data-live-search="true"
                data-actions-box="true"
                title="{{ t('Provider') }}"
                data-header="{{ t('Select Provider') }}"
                data-width="500"
                data-style="btn-primary">
            <option value="">{{ t('None') }}</option>
            @foreach($products as $product)
                <option value="{{ $product->key }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
    @if ($errors->any())
        <div class="row mt-3 alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row mt-5">
        <button type="submit" class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Submit') }}</button>
    </div>
</form>
