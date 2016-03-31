@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Manage OCR files')


@section('main-content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ trans('pages.phpinfo') }}</div>

                    <div class="panel-body">
                        {{ phpinfo() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection