@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Create Category or FAQ')


@section('main-content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Category</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {!! Form::open([
                        'route' => ['admin.faqs.category.store'],
                        'method' => 'post',
                        'role' => 'form'
                    ]) !!}
                        <div class="box-body">
                            <div class="form-group required {{ ($errors->has('name')) ? 'has-error' : '' }}" for="name">
                                {!! Form::label('name', 'Name', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('name', null, array('class' => 'form-control', 'placeholder' => 'Name')) !!}
                                </div>
                                {{ ($errors->has('name') ? $errors->first('name') : '') }}
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            {{ Form::submit('Submit', ['class' => 'btn btn-primary']) }}
                        </div>
                    {{ Form::close() }}
                </div>
                <!-- /.box -->
            </div>
            <!--/.col (left) -->
            <!-- right column -->
            <div class="col-md-6">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Create FAQ</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form class="form-horizontal">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="question" class="col-sm-2 control-label">Question</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="question" placeholder="Question">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="answer" class="col-sm-2 control-label">Answer</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" rows="3" placeholder="Enter question..."></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">Submit</button>
                        </div>
                        <!-- /.box-footer -->
                    </form>
                </div>
                <!-- /.box -->
            </div>
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
    </div>
@endsection