@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Rapid Records Import') }}
@stop

@section('custom-style')
    <style>
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary {
            color: #ffffff;
        }

        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:hover {
            color: #c83f29;
        }
    </style>
@endsection
{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Records Update') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card white box-shadow py-5 my-5 p-sm-5">
                <form action="{{ route('admin.ingest.selected') }}"
                      method="post" role="form" id="update-rapid-file">
                    @method('PUT')
                    @csrf
                    <div class="row mb-5">
                        @if($errors->any())
                            <div class="col-md-6 m-auto text-center">
                                @if($errors->any())
                                    <div class="text-danger">{{ t('At least one selection needs to be made.') }}</div>
                                    @endif
                            </div>
                        @endif
                    </div>
                    <div class="row text-center">
                        <div class="form-group col-md-12">
                        @foreach($groupedHeaders as $index => $column)
                                <select class="selectpicker col-sm-2 mb-2" name="{{ $index }}[]"
                                        data-live-search="true"
                                        data-actions-box="true"
                                        multiple
                                        title="Choose {{ $index }}"
                                        data-header="Select a column"
                                        data-width="252"
                                        data-style="btn-primary">
                                    @foreach($column as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                        @endforeach
                        </div>
                    </div>
                    <div class="row text-center mt-4">
                        <input type="hidden" name="filePath" value="{{ $filePath }}">
                        <input type="hidden" name="fileName" value="{{ $fileName }}">
                        <input type="hidden" name="fileOrigName" value="{{ $fileOrigName }}">
                        <button type="submit"
                                class="btn btn-primary pl-4 pr-4 mt-5 text-uppercase m-auto">{{ t('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection