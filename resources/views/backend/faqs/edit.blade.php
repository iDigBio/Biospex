@extends('backend.layouts.app')

@section('htmlheader_title')
    Edit Category or FAQ
@endsection

@section('contentheader_title', 'Edit Category or FAQ')


@section('main-content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Category</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {!! Form::open([
                        'route' => ['admin.faqs.categories.update', $category->id],
                        'method' => 'put',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group required {{ ($errors->has('name')) ? 'has-error' : '' }}" for="name">
                            {!! Form::label('name', 'Name', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('name', $category->label, ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                                {{ ($errors->has('name') ? $errors->first('name') : '') }}
                            </div>
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
                        <h3 class="box-title">Edit FAQ</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {!! Form::open([
                        'route' => ['admin.faqs.update', $category->id, $faq === null ? 0 : $faq->id],
                        'method' => 'put',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group required {{ ($errors->has('faq_category_id')) ? 'has-error' : '' }}" for="faq_category_id">
                            {!! Form::label('faq_category_id', 'Category', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::select('faq_category_id', $categories, $category->id, ['class' => 'form-control']) !!}
                                {{ ($errors->has('faq_category_id') ? $errors->first('faq_category_id') : '') }}
                            </div>
                        </div>
                        <div class="form-group required {{ ($errors->has('question')) ? 'has-error' : '' }}" for="question">
                            {!! Form::label('question', 'Question', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('question', $faq === null ? '': $faq->question, ['class' => 'form-control', 'placeholder' => 'Question']) !!}
                                {{ ($errors->has('question') ? $errors->first('question') : '') }}
                            </div>
                        </div>
                        <div class="form-group required {{ ($errors->has('answer')) ? 'has-error' : '' }}">
                            {!! Form::label('answer', 'Answer', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('answer', $faq === null ? '': $faq->answer, ['class' => 'form-control ckeditor', 'placeholder' => 'Answer']) !!}
                                {{ ($errors->has('answer') ? $errors->first('answer') : '') }}
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
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
    </div>
@endsection