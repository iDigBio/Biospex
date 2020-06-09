@extends('common.expedition-loop')
@section('expedition-icons')
    {!! $expedition->present()->expedition_show_icon !!}
    {!! $expedition->present()->expedition_edit_icon !!}
    {!! $expedition->present()->expedition_clone_icon !!}
    @can('isOwner', $expedition->project->group)
        {!! $expedition->present()->expedition_delete_icon !!}
    @endcan
@endsection