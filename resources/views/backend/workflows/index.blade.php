@extends('backend.layouts.app')

@section('htmlheader_title')
    Workflows
@endsection

@section('contentheader_title', 'Workflows')


@section('main-content')
    <div class="row">
        <!-- right column -->
        <div class="col-xs-12">
            <div class="box box-primary {!! Html::collapse(['admin.workflows.edit', 'admin.workflows.create']) !!} box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ isset($workflow->id) ? 'Edit Workflow' : 'Create Workflow' }}
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa {!! Html::setIconByRoute(['admin.workflows.edit', 'admin.workflows.create'], ['fa-minus', 'fa-plus']) !!}"></i>
                        </button>
                    </h3>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <!-- /.box-header -->
                        <!-- form start -->
                        {!! Form::open([
                            'route' => isset($workflow->id) ? ['admin.workflows.update', $workflow->id] : ['admin.workflows.store'],
                            'method' => isset($workflow->id) ? 'put' : 'post',
                            'role' => 'form',
                            'id' => 'workflow',
                            'class' => 'form-horizontal'
                        ]) !!}
                        <div class="box-body">
                            @if ($errors->any())
                                @foreach($errors->all() as $error)
                                    <div class="red">{{$error}}</div>
                                @endforeach
                            @endif
                            <div class="form-group required" for="title">
                                {!! Form::label('title', 'Title', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('title', isset($workflow->title) ? $workflow->title : null, ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                                </div>
                            </div>
                            <div class="form-group required">
                                {!! Form::label('workflow', 'Workflow', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    <div class="box-body col-xs-6">
                                        Actors:
                                        <ul class="todo-list source">
                                            @include('backend.workflows.partials.actors')
                                        </ul>
                                    </div>
                                    <div class="box-body col-xs-6">
                                        Actor Workflow:
                                        <ul class="todo-list target">
                                            @if(isset($workflow->actors))
                                                @include('backend.workflows.partials.target-actors')
                                            @else
                                                <li class="placeholder">Drop Actors here</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" for="enabled">
                            {!! Form::label('enabled', 'Enabled', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::checkbox('enabled', '1', isset($workflow->enabled) ? $workflow->enabled : 0) !!}
                            </div>
                        </div>
                        {!! Form::submit('Submit', ['class' => 'btn btn-primary pull-right']) !!}
                    </div>

                    {!! Form::close() !!}
                </div>
                <!-- /.box -->
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-info">
                <h3 class="box-title">Current Workflows</h3>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <colgroup>
                            <col class="col-md-10">
                            <col class="col-md-1">
                            <col class="col-md-1">
                        </colgroup>
                        <tr>
                            <th>Workflow</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        @include('backend.workflows.partials.workflows')
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-info">
                <h3 class="box-title">Deleted Workflows</h3>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <colgroup>
                            <col class="col-md-10">
                            <col class="col-md-1">
                            <col class="col-md-1">
                        </colgroup>
                        <tr>
                            <th>Workflow</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        @include('backend.workflows.partials.trashed')
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection