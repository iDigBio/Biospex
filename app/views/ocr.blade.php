@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{trans('pages.ocr_files')}}
@stop

{{-- Content --}}
@section('content')
    <div class="col-md-10 col-md-offset-1">
        <h3>{{ trans('pages.ocr_files') }}</h3>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                @foreach ($elements as $item)
                    @if (preg_match('/\.json/i', $item->nodeValue))
                    <tr>
                        <td><?php echo $item->nodeValue; ?></td>
                        <td class="nowrap">
                            <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ URL::route('delete.ocr', [$item->nodeValue]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button>
                        </td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
