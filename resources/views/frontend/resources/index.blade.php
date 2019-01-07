@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.resources')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top30">
        <div class="col-xs-8 col-xs-offset-2">
            <h2>{{ trans('pages.resources') }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="col-xs-8">
                <hr/>
                @foreach($resources as $resource)
                    @include('front.resources.partials.resources')
                @endforeach

            </div>
        </div>
    </div>
@endsection