@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
<div class="jumbotron">
    <h3>{{ $project->title }}</h3>
    <p>@lang('pages.advertise_title')</p>
    <button title="@lang('pages.downloadTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('admin.advertises.show', [$project->id]) }}'"><span class="fa fa-download fa-lrg"></span> @lang('pages.download') </button>
</div>
<div class="col-xs-12">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">@lang('pages.advertise_fields')</h3>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover dataTable">
                    <thead>
                    <tr>
                        <th>@lang('pages.field')</th>
                        <th>@lang('pages.value')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($project->advertise as $field => $value)
                        <tr>
                            <td>{{ $field }}</td>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
