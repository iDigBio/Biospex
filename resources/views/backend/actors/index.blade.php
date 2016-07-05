@extends('backend.layouts.app')

@section('htmlheader_title')
    Actors
@endsection

@section('contentheader_title', 'Actors')


@section('main-content')
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <div class="box box-primary {!! Html::collapse(['admin.actors.edit', 'admin.actors.create']) !!} box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ isset($actor->id) ? 'Edit Actor' : 'Create Actor' }}
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa {!! Html::setIconByRoute(['admin.actors.edit', 'admin.actors.create'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                            'route' => isset($actor->id) ? ['admin.actors.update', $actor->id] : ['admin.actors.store'],
                            'method' => isset($actor->id) ? 'put' : 'post',
                            'role' => 'form',
                            'class' => 'form-horizontal'
                        ]) !!}
                        <div class="box-body">
                            <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                                {!! Form::label('title', 'Title', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('title', isset($actor->title) ? $actor->title : null, ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                                    {{ ($errors->has('title') ? $errors->first('title') : '') }}
                                </div>
                            </div>
                            <div class="form-group required {{ ($errors->has('url')) ? 'has-error' : '' }}" for="url">
                                {!! Form::label('url', 'Url', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('url', isset($actor->url) ? $actor->url : null, ['class' => 'form-control', 'placeholder' => 'Url']) !!}
                                    {{ ($errors->has('url') ? $errors->first('url') : '') }}
                                </div>
                            </div>
                            <div class="form-group required {{ ($errors->has('class')) ? 'has-error' : '' }}" for="class">
                                {!! Form::label('class', 'Class Name', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('class', isset($actor->class) ? $actor->class : null, ['class' => 'form-control', 'placeholder' => 'Class Name']) !!}
                                    {{ ($errors->has('class') ? $errors->first('class') : '') }}
                                </div>
                            </div>
                            <div class="form-group" for="private">
                                {!! Form::label('private', 'Private', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::checkbox('private', '1', isset($actor->private) ? $actor->private : 0) !!}
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            {{ Form::submit('Submit', ['class' => 'btn btn-primary pull-right']) }}
                        </div>
                        <!-- /.box-footer -->
                        </form>
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!--/.col (right) -->
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Url</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        @include('backend.actors.partials.actors')
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection