@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.resources')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top-buffer">
        <div class="col-xs-8 col-xs-offset-2">
            <h2>{{ trans('pages.resources') }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1">
            <hr/>
            <div class="col-xs-8">
                <!-- Tab panes -->
                <div class="tab-content">
                    @foreach($resources as $resource)
                        @include('frontend.resources.partials.resources')
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection