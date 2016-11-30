<div class="row">
    <!-- right column -->
    <div class="col-md-8">
        <div class="box box-primary {{ $errors->any() ? '' : 'collapsed-box' }} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Create Group
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-plus "></i>
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
                        'route' => 'admin.groups.store',
                        'method' => 'post',
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