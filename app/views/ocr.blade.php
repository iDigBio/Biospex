@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{trans('pages.ocr_files')}}
@stop

{{-- Content --}}
@section('content')
    <h4>{{ trans('pages.ocr_files') }}</h4>
    <div class="well">
        {{ Form::open(array(
        'action' => array('ServerInfoController@ocr'),
        'method' => 'post',
        'class' => 'form-horizontal',
        'role' => 'form'
        )) }}

        <div class="form-group col-md-offset-4">
            <div class="col-sm-6">
                {{ Form::checkbox('selectall', null, null, ['id'=>'selectall']) }}
                {{ Form::label('Select All', 'Select All', array('id'=>'','class'=>'')) }}
            </div>
        </div>

        @foreach ($elements as $item)
            @if (preg_match('/\.json/i', $item->nodeValue))
                <div class="form-group col-md-offset-4">
                    <div class="col-sm-6">
                        {{ Form::checkbox('files[]', $item->nodeValue, null, ['id'=>$item->nodeValue]) }}
                        {{ Form::label($item->nodeValue, $item->nodeValue,array('id'=>'','class'=>'')) }}
                    </div>
                </div>
            @endif
        @endforeach

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                {{ Form::submit(trans('buttons.delete'), array('class' => 'btn btn-danger')) }}
            </div>
        </div>
        {{ Form::close()}}
    </div>
@stop
