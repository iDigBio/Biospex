@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ $expedition->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('webauth.expeditions.show.title', $expedition, 'Downloads') !!}

    <div class="jumbotron">
        <h2>{{ $expedition->title }} @lang('pages.transcriptions')</h2>
        <p>{{ $expedition->description }}</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <button title="Back to Expedition Details" class="btn btn-info btn-xs" type="button"
                            onClick="location.href='{{ route('webauth.expeditions.show', [$expedition->project->id, $expedition->id]) }}'">
                        <span class="glyphicon glyphicon-eye-open"></span> Return
                    </button>
                </div>
            </div>
        </div>
    </div>

    @foreach ($expedition->actors as $actor)
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ $actor->title }}</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover dataTable">
                            <thead>
                            <tr>
                                <th>{{ trans('pages.download_type') }}</th>
                                <th>{{ trans('pages.filename') }}</th>
                                <th>{{ trans('pages.filesize') }}</th>
                                <th>{{ trans('pages.created') }}</th>
                                <th>{{ trans('pages.updated') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($actor->downloads as $download)
                                @if ( ! empty($download))
                                    @include('frontend.downloads.partials.tablerow')
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop