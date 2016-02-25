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
                    <div class="panel-heading">{{ trans('pages.ocr_files') }}</div>

                    <div class="panel-body">
                        {!!  Form::open([
                        'route' => ['ocr.post.index'],
                        'method' => 'post',
                        'class' => 'form-horizontal',
                        'role' => 'form'
                        ]) !!}

                        <div class="form-group col-md-offset-4">
                            <div class="col-sm-6">
                                {!! Form::checkbox('selectall', null, null, ['id'=>'selectall']) !!}
                                {!! Form::label('Select All', 'Select All', array('id'=>'','class'=>'')) !!}
                            </div>
                        </div>

                        @foreach ($elements as $item)
                            @if (preg_match('/\.json/i', $item->nodeValue))
                                <div class="form-group col-md-offset-4">
                                    <div class="col-sm-6">
                                        {!! Form::checkbox('files[]', $item->nodeValue, null, ['id' => $item->nodeValue, 'class' => 'checkbox-all']) !!}
                                        {!! Form::label($item->nodeValue, $item->nodeValue, ['id'=>'','class'=>'']) !!}
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                {!! Form::submit(trans('buttons.delete'), array('class' => 'btn btn-danger')) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection