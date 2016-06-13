@extends('backend.layouts.app')

@section('htmlheader_title')
    Create Category or FAQ
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
                        'role' => 'form',
                        'class' => 'form-horizontal'
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
                    {!! Form::open([
                        'route' => ['admin.faqs.store'],
                        'method' => 'post',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                        <div class="box-body">
                            <div class="form-group required {{ ($errors->has('faq_category_id')) ? 'has-error' : '' }}" for="faq_category_id">
                                {!! Form::label('faq_category_id', 'Category', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::select('faq_category_id', $categories, $category, ['class' => 'form-control']) !!}
                                </div>
                                {{ ($errors->has('faq_category_id') ? $errors->first('faq_category_id') : '') }}
                            </div>
                            <div class="form-group required {{ ($errors->has('question')) ? 'has-error' : '' }}" for="question">
                                {!! Form::label('question', 'Question', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('question', null, ['class' => 'form-control', 'placeholder' => 'Question']) !!}
                                </div>
                                {{ ($errors->has('question') ? $errors->first('question') : '') }}
                            </div>
                            <div class="form-group required {{ ($errors->has('answer')) ? 'has-error' : '' }}">
                                {!! Form::label('answer', 'Answer', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::textarea('answer', null, ['class' => 'form-control ckeditor', 'placeholder' => 'Answer']) !!}
                                </div>
                                {{ ($errors->has('answer') ? $errors->first('answer') : '') }}
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
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
    </div>
@endsection