@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.team')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top-buffer">
        <div class="col-md-10 col-md-offset-1">
            <h3>{{ trans('pages.team') }}</h3>
        </div>
        <div class="col-md-10 col-md-offset-1">

            <ul>
                <li>Austin Mast</li>
                <li>Greg Riccardi</li>
            </ul>
        </div>
    </div>
@endsection