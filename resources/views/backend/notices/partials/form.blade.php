<div class="row">
    <!-- right column -->
    <div class="col-xs-12">
        <div class="box box-primary {!! Html::collapse(['admin.notices.edit', 'admin.notices.create']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($notices->id) ? 'Edit Notice' : 'Create Notice' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.notices.edit', 'admin.notices.create'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                        'route' => isset($notice->id) ? ['admin.notices.update', $notice->id] : ['admin.notices.store'],
                        'method' => isset($notice->id) ? 'put' : 'post',
                        'role' => 'form',
                        'id' => 'workflow',
                        'class' => 'form-horizontal'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group required" for="message">
                            {!! Form::label('message', 'Message', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('message', isset($notice->message) ? $notice->message : null, ['class' => 'form-control', 'placeholder' => 'Message']) !!}
                                {!! ($errors->has('message') ? $errors->first('message') : '') !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group" for="enabled">
                        {!! Form::label('enabled', 'Enabled', ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::checkbox('enabled', '1', isset($notice->enabled) ? $notice->enabled : 0, ['class' => 'minimal']) !!}
                        </div>
                    </div>
                    {!! Form::submit('Submit', ['class' => 'btn btn-primary pull-right']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            <!-- /.box -->
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>