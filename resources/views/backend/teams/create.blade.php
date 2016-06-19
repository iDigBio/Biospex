@extends('backend.layouts.app')

@section('htmlheader_title')
    Create Team Category or Member
@endsection

@section('contentheader_title', 'Create Category or Team Member')


@section('main-content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Team Category</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {!! Form::open([
                        'route' => ['admin.teams.category.store'],
                        'method' => 'post',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                        <div class="box-body">
                            <div class="form-group required {{ ($errors->has('name')) ? 'has-error' : '' }}" for="name">
                                {!! Form::label('name', 'Name', ['class' => 'col-sm-2 control-label']) !!}
                                <div class="col-sm-10">
                                    {!! Form::text('name', null, array('class' => 'form-control', 'placeholder' => 'Name')) !!}
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
                        <h3 class="box-title">Create Member</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {!! Form::open([
                        'route' => ['admin.teams.store'],
                        'method' => 'post',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                        <div class="box-body">
                            <div class="form-group required {{ ($errors->has('team_category_id')) ? 'has-error' : '' }}" for="team_category_id">
                                {!! Form::label('team_category_id', 'Category', ['class' => 'col-sm-3 control-label']) !!}
                                <div class="col-sm-9">
                                    {!! Form::select('team_category_id', $categories, $category, ['class' => 'form-control']) !!}
                                    {{ ($errors->has('team_category_id') ? $errors->first('team_category_id') : '') }}
                                </div>
                            </div>
                            <div class="form-group required {{ ($errors->has('first_name')) ? 'has-error' : '' }}" for="first_name">
                                {!! Form::label('first_name', 'First Name', ['class' => 'col-sm-3 control-label']) !!}
                                <div class="col-sm-9">
                                    {!! Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => 'First Name']) !!}
                                    {{ ($errors->has('first_name') ? $errors->first('first_name') : '') }}
                                </div>
                            </div>
                            <div class="form-group required {{ ($errors->has('last_name')) ? 'has-error' : '' }}" for="last_name">
                                {!! Form::label('last_name', 'Last Name', ['class' => 'col-sm-3 control-label']) !!}
                                <div class="col-sm-9">
                                    {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => 'Last Name']) !!}
                                    {{ ($errors->has('last_name') ? $errors->first('last_name') : '') }}
                                </div>
                            </div>
                            <div class="form-group required {{ ($errors->has('email')) ? 'has-error' : '' }}" for="email">
                                {!! Form::label('email', 'Email', ['class' => 'col-sm-3 control-label']) !!}
                                <div class="col-sm-9">
                                    {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Email']) !!}
                                    {{ ($errors->has('email') ? $errors->first('email') : '') }}
                                </div>
                            </div>
                            <div class="form-group required {{ ($errors->has('institution')) ? 'has-error' : '' }}" for="institution">
                                {!! Form::label('institution', 'Institution', ['class' => 'col-sm-3 control-label']) !!}
                                <div class="col-sm-9">
                                    {!! Form::text('institution', null, ['class' => 'form-control', 'placeholder' => 'Institution']) !!}
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
            <!--/.col (right) -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
    </div>
@endsection