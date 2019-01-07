@extends('backend.layouts.app')

@section('htmlheader_title')
    Manage Teams
@endsection

@section('contentheader_title', 'Manage Teams')


@section('main-content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <div class="box box-primary {!! Html::collapse(['admin.teams.categories.edit']) !!} box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ isset($category->id) ? 'Edit Team Category' : 'Add Team Category' }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa {!! Html::setIconByRoute(['admin.teams.categories.edit'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                                'route' => isset($category->id) ? ['admin.teams.categories.update', $category->id, $teamId] : ['admin.teams.category.store', $categoryId],
                                'method' => isset($category->id) ? 'put' : 'post',
                                'role' => 'form',
                                'class' => 'form-horizontal'
                            ]) !!}
                            <div class="box-body">
                                <div class="form-group required {{ ($errors->has('name')) ? 'has-error' : '' }}" for="name">
                                    {!! Form::label('name', 'Name', ['class' => 'col-sm-2 control-label']) !!}
                                    <div class="col-sm-10">
                                        {!! Form::text('name', isset($category->name) ? $category->name : null, array('class' => 'form-control', 'placeholder' => 'Name')) !!}
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
                <div class="box box-primary {!! Html::collapse(['admin.teams.edit', 'admin.teams.create']) !!} box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ isset($team->id) ? 'Update Team Member' : 'Create Team Member' }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa {!! Html::setIconByRoute(['admin.teams.edit', 'admin.teams.create'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                                'route' => isset($team->id) ? ['admin.teams.update', $categoryId, $team->id] : ['admin.teams.store', $categoryId],
                                'method' => isset($team->id) ? 'put' : 'post',
                                'role' => 'form',
                                'class' => 'form-horizontal'
                            ]) !!}
                            <div class="box-body">
                                <div class="form-group required {{ ($errors->has('team_category_id')) ? 'has-error' : '' }}"
                                     for="team_category_id">
                                    {!! Form::label('team_category_id', 'Category', ['class' => 'col-sm-3 control-label']) !!}
                                    <div class="col-sm-9">
                                        {!! Form::select('team_category_id', $select, $categoryId ?:null, ['class' => 'form-control']) !!}
                                        {{ ($errors->has('team_category_id') ? $errors->first('team_category_id') : '') }}
                                    </div>
                                </div>
                                <div class="form-group required {{ ($errors->has('first_name')) ? 'has-error' : '' }}" for="first_name">
                                    {!! Form::label('first_name', 'First Name', ['class' => 'col-sm-3 control-label']) !!}
                                    <div class="col-sm-9">
                                        {!! Form::text('first_name', isset($team->first_name) ? $team->first_name : null, ['class' => 'form-control', 'placeholder' => 'First Name']) !!}
                                        {{ ($errors->has('first_name') ? $errors->first('first_name') : '') }}
                                    </div>
                                </div>
                                <div class="form-group required {{ ($errors->has('last_name')) ? 'has-error' : '' }}" for="last_name">
                                    {!! Form::label('last_name', 'Last Name', ['class' => 'col-sm-3 control-label']) !!}
                                    <div class="col-sm-9">
                                        {!! Form::text('last_name', isset($team->last_name) ? $team->last_name : null, ['class' => 'form-control', 'placeholder' => 'Last Name']) !!}
                                        {{ ($errors->has('last_name') ? $errors->first('last_name') : '') }}
                                    </div>
                                </div>
                                <div class="form-group required {{ ($errors->has('email')) ? 'has-error' : '' }}" for="email">
                                    {!! Form::label('email', 'Email', ['class' => 'col-sm-3 control-label']) !!}
                                    <div class="col-sm-9">
                                        {!! Form::text('email', isset($team->email) ? $team->email : null, ['class' => 'form-control', 'placeholder' => 'Email']) !!}
                                        {{ ($errors->has('email') ? $errors->first('email') : '') }}
                                    </div>
                                </div>
                                <div class="form-group required {{ ($errors->has('institution')) ? 'has-error' : '' }}" for="institution">
                                    {!! Form::label('institution', 'Institution', ['class' => 'col-sm-3 control-label']) !!}
                                    <div class="col-sm-9">
                                        {!! Form::text('institution', isset($team->institution) ? $team->institution : null, ['class' => 'form-control', 'placeholder' => 'Institution']) !!}
                                        {{ ($errors->has('institution') ? $errors->first('institution') : '') }}
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
                    @include('backend.teams.partials.categories')
                @endforeach
            </div>
        </div>
    </section>
@endsection