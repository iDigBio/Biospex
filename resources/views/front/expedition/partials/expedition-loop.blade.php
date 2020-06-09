@extends('common.expedition-loop')
@section('expedition-icons')
    {!! $expedition->project->present()->project_page_icon !!}
    @isset($expedition->panoptesProject)
        @if ($expedition->nfnActor->pivot->completed === 0)
            {!! $expedition->panoptesProject->present()->url !!}
        @endif
    @endisset
@endsection