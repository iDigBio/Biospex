@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.team_biospex')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top30">
        <div class="col-xs-8 col-xs-offset-2">
            <h2>{{ trans('pages.team_biospex') }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-10 col-lg-offset-1">
            @foreach($categories as $category)
                @include('frontend.teams.partials.categories')
            @endforeach
        </div>
    </div>
@endsection
