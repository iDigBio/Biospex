@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.group') }} {{ $group->title }}
@stop

@section('custom-style')
    <link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css"
          rel="stylesheet"/>
@endsection
{{-- Content --}}
@section('content')
    @include('admin.group.partials.group-panel')

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card white px-4 box-shadow h-100">
                <h3 class="text-center pt-4">{{ __('pages.group') }} {{ __('pages.members') }}</h3>
                <hr>
                <div class="color-action text-center">{{ __('pages.table_sort') }}</div>
                <div class="row card-body">
                    <p>{{ __('pages.group') }} {{ __('pages.owner') }}: {{ $group->owner->present()->full_name_or_email }}</p>
                    @if($group->users->isEmpty())
                        <p class="text-center">{{ __('') }}</p>
                    @else
                        <table id="members-tbl" class="table table-striped table-bordered dt-responsive nowrap"
                               style="width:100%; font-size: .8rem">
                            <thead>
                            <tr>
                                <th style="width: 5%"></th>
                                <th>{{ __('pages.member') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @each('admin.group.partials.member-loop', $group->users, 'user')
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card white px-4 box-shadow h-100">
                <h3 class="text-center pt-4">{{ __('pages.group') }} {{ __('pages.projects') }}</h3>
                <hr>
                <div class="color-action text-center">{{ __('pages.table_sort') }}</div>
                <div class="row card-body">
                    @if($group->projects->isEmpty())
                        <p class="text-center">{{ __('pages.projects_none') }}</p>
                    @else
                        <table id="projects-tbl" class="table table-striped table-bordered dt-responsive nowrap"
                               style="width:100%; font-size: .8rem">
                            <thead>
                            <tr>
                                <th>{{ __('pages.title') }}</th>
                                <th>{{ __('pages.description') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @each('admin.group.partials.project-loop', $group->projects, 'project')
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.invite-modal')
@endsection
@section('custom-script')
    <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    @if($group->users->isNotEmpty())
        <script>
            $('#members-tbl').DataTable();
        </script>
    @endif
    @if($group->projects->isNotEmpty())
        <script>
            $('#projects-tbl').DataTable();
        </script>
    @endif
@endsection