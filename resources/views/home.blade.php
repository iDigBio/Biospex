@extends('layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Product') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Rapid Record Product') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-12 m-auto">
            <div class="card white box-shadow py-5 my-5 p-sm-5">
                <div class="row mt-5">
                    <div class="col-sm-10 mx-auto">
                        <table id="dwc" class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>Provider</th>
                                <th>File</th>
                                <th>Date</th>
                                <th>Download</th>
                            </tr>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->key . '.zip' }}</td>
                                    <td>{{ \App\Facades\DateHelper::formatDate($product->updated_at, 'Y-m-d') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary pl-4 pr-4 downloadProduct"
                                                data-hover="tooltip"
                                                data-url="{{ $product->present()->download }}"
                                                title="{{ t('Download Product File') }}">{{ t('Download') }}</button>
                                    </td>
                                </tr>
                            @endforeach
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection