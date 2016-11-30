<div class="row">
    <!-- right column -->
    <div class="col-md-8">
        <div class="box box-primary {!! Html::collapse(['admin.groups.edit', 'admin.groups.create']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($group->id) ? 'Edit Group' : 'Create Group' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.group.edit', 'admin.group.create'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                        'route' => isset($group->id) ? ['admin.groups.update', $group->id] : ['admin.groups.store'],
                        'method' => isset($group->id) ? 'put' : 'post',
                        'role' => 'form',
                        'class' => 'form-horizontal',
                        'enctype' => 'multipart/form-data'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-users fa-lg"></i></span>
                                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans('pages.name'), 'required']) !!}
                            </div>
                            {{ ($errors->has('name') ?  $errors->first('name') : '') }}
                        </div>
                        {!! Form::hidden('owner', $user->id) !!}

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