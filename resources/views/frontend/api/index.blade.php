@extends('frontend.api.default')

{{-- Web site Title --}}
@section('title')
    @parent
    Biospex API
@stop

{{-- Content --}}
@section('content')
    @apiuser
    <div class="row">
        <div id="app" class="col-md-8 col-md-offset-2 top30">
            <!-- let people make clients -->
            <passport-clients></passport-clients>
        </div>
    </div>
    @endapiuser
@endsection
