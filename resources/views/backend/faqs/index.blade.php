@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Manage FAQs')


@section('main-content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <div class="box box-primary {!! Html::collapse(['admin.faqs.categories.edit']) !!} box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ isset($category->id) ? 'Edit FAQ Category' : 'Add FAQ Category' }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa {!! Html::setIconByRoute(['admin.faqs.categories.edit'], ['fa-minus', 'fa-plus']) !!}"></i>
                            </button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <!-- /.box-header -->
                            <!-- form start -->
                            {!! Form::open([
                                'route' => isset($category->id) ? ['admin.faqs.categories.update', $category->id, $faqId] : ['admin.faqs.category.store'],
                                'method' => isset($category->id) ? 'put' : 'post',
                                'role' => 'form',
                                'class' => 'form-horizontal'
                            ]) !!}
                            <div class="box-body">
                                <div class="form-group required {{ ($errors->has('name')) ? 'has-error' : '' }}" for="name">
                                    {!! Form::label('name', 'Name', ['class' => 'col-sm-2 control-label']) !!}
                                    <div class="col-sm-10">
                                        {!! Form::text('name', isset($category->label) ? $category->label : null, array('class' => 'form-control', 'placeholder' => 'Name')) !!}
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
                    </div>
                    <!-- /.box-body -->
                </div>

                <!-- /.box -->
            </div>
            <!--/.col (left) -->
            <!-- right column -->
            <div class="col-md-6">
                <div class="box box-primary {!! Html::collapse(['admin.faqs.edit', 'admin.faqs.create']) !!} box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ isset($faq->id) ? 'Update FAQ' : 'Create FAQ' }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa {!! Html::setIconByRoute(['admin.faqs.edit', 'admin.faqs.create'], ['fa-minus', 'fa-plus']) !!}"></i>
                            </button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- Horizontal Form -->
                        <div class="box box-primary">
                            <!-- /.box-header -->
                            <!-- form start -->
                            {!! Form::open([
                                'route' => isset($faq->id) ? ['admin.faqs.update', $categoryId, $faq->id] : ['admin.faqs.store', $categoryId],
                                'method' => isset($faq->id) ? 'put' : 'post',
                                'role' => 'form',
                                'class' => 'form-horizontal'
                            ]) !!}
                            <div class="box-body">
                                <div class="form-group required {{ ($errors->has('faq_category_id')) ? 'has-error' : '' }}"
                                     for="faq_category_id">
                                    {!! Form::label('faq_category_id', 'Category', ['class' => 'col-sm-2 control-label']) !!}
                                    <div class="col-sm-10">
                                        {!! Form::select('faq_category_id', $select, $categoryId ?:null, ['class' => 'form-control']) !!}
                                        {{ ($errors->has('faq_category_id') ? $errors->first('faq_category_id') : '') }}
                                    </div>
                                </div>
                                <div class="form-group required {{ ($errors->has('question')) ? 'has-error' : '' }}"
                                     for="question">
                                    {!! Form::label('question', 'Question', ['class' => 'col-sm-2 control-label']) !!}
                                    <div class="col-sm-10">
                                        {!! Form::text('question', isset($faq->question) ? $faq->question : null, ['class' => 'form-control', 'placeholder' => 'Question']) !!}
                                        {{ ($errors->has('question') ? $errors->first('question') : '') }}
                                    </div>
                                </div>
                                <div class="form-group required {{ ($errors->has('answer')) ? 'has-error' : '' }}">
                                    {!! Form::label('answer', 'Answer', ['class' => 'col-sm-2 control-label']) !!}
                                    <div class="col-sm-10">
                                        {!! Form::textarea('answer', isset($faq->answer) ? $faq->answer : null, ['class' => 'form-control ckeditor', 'placeholder' => 'Answer']) !!}
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
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-xs-12">
                @foreach($categories as $category)
                    @include('backend.faqs.partials.faqcategories')
                @endforeach
            </div>
        </div>
    </section>
@endsection