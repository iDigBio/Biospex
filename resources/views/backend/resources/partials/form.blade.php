<div class="row">
    <!-- right column -->
    <div class="col-md-12">
        <div class="box box-primary {!! Html::collapse(['admin.resources.edit', 'admin.resources.create']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($resource->id) ? 'Edit Resource' : 'Create Resource' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.resources.edit', 'admin.resources.create'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                        'route' => isset($resource->id) ? ['admin.resources.update', $resource->id] : ['admin.resources.store'],
                        'method' => isset($resource->id) ? 'put' : 'post',
                        'role' => 'form',
                        'class' => 'form-horizontal',
                        'enctype' => 'multipart/form-data'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                            {!! Form::label('title', 'Title', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('title', isset($resource->title) ? $resource->title : null, ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                                {{ ($errors->has('title') ? $errors->first('title') : '') }}
                            </div>
                        </div>
                        <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}" for="description">
                            {!! Form::label('description', 'Description', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::textarea('description', isset($resource->description) ? $resource->description : null, ['class' => 'form-control', 'placeholder' => 'Description']) !!}
                                {{ ($errors->has('description') ? $errors->first('description') : '') }}
                            </div>
                        </div>
                        <div class="form-group {{ ($errors->has('document')) ? 'has-error' : '' }}" for="document">
                            {!! Form::label('document', 'Document', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-4">
                                {{ isset($resource->document) ? $resource->document : null }}
                                {!! Form::file('document') !!}
                                {{ ($errors->has('document') ? $errors->first('document') : '') }}
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