<hr class="header mx-auto" style="width:500px;">
<form action="{{ route('admin.export.create', [$destination]) }}"
      method="post" role="form" class="exportFrm">
    @csrf
    <input type="hidden" name="entries" value="{{ old('entries', $data['count']) }}">
    <input type="hidden" name="exportDestination" value="{{ $destination }}">
    @isset($data['frmName'])
        @include('export.partials.export-delete')
    @endisset
    <div class="row">
        <div class="col-sm-10 mx-auto text-center">
            @include('export.partials.export-type')
        </div>
    </div>
    @if(isset($data['fields']))
        @include('export.partials.export-form-fields')
    @else
        @include('export.partials.export-form-generic')
    @endif
</form>
@if(isset($data['fields']))
<div class="row default mt-2" style="display: none">
    <input type="hidden" class="hidden" id="order" data-id="999" name="exportFields[999][order]" value="">
    @include('export.partials.export-field-select-default')
    @include('export.partials.tags-select-fields-default')
</div>
@endif