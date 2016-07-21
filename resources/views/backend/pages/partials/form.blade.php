@if(isset($page))
<div class="row">
    <!-- right column -->
    <div class="col-md-12">
        <div class="box box-primary {!! Html::collapse(['admin.pages.edit']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Edit HTML
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.pages.edit'], ['fa-minus', 'fa-plus']) !!}"></i>
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
                        'route' => ['admin.pages.update', $page->id],
                        'method' => 'put',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group required {{ ($errors->has('value')) ? 'has-error' : '' }}"
                             for="value">
                            {!! Form::label('value', 'HTML', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('value', $page->value, ['class' => 'form-control']) !!}
                                {{ ($errors->has('value') ? $errors->first('value') : '') }}
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
@endif