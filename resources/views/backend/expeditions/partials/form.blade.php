<div class="row">
    <!-- right column -->
    <div class="col-md-12">
        <div class="box box-primary {!! Html::collapse(['admin.expeditions.edit', 'admin.expeditions.*']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($editProject->id) ? 'Edit Expedition' : 'Create Expedition' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.expeditions.edit', 'admin.expeditions.*'], ['fa-minus', 'fa-plus']) !!}"></i>
                    </button>
                </h3>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="panel-body">
                    {!! Form::open([
                    'route' => isset($editExpedition->id) ? ['admin.expeditions.update', $editExpedition->id] : ['admin.expeditions.store'],
                    'method' => isset($editExpedition->id) ? 'put' : 'post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}

                    <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                        {!! Form::label('title', trans('forms.title'), ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::text('title', isset($editExpedition->title) ? $editExpedition->title : null, ['class' => 'form-control', 'placeholder' => trans('forms.title')]) !!}
                            {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        </div>
                    </div>

                    <div class="form-group required {{ ($errors->has('description')) ? 'has-error' : '' }}">
                        {!! Form::label('description', trans('forms.description'), ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::text('description', isset($editExpedition->description) ? $editExpedition->description : null, ['class' => 'form-control', 'placeholder' => trans('forms.description')]) !!}
                            {{ ($errors->has('description') ? $errors->first('description') : '') }}
                        </div>
                    </div>

                    <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                        {!! Form::label('keywords', trans('forms.keywords'), ['class' => 'col-sm-2 control-label']) !!}
                        <div class="col-sm-10">
                            {!! Form::text('keywords', isset($editExpedition->keywords) ? $editExpedition->keywords : null, ['class' => 'form-control', 'placeholder' => trans('forms.keywords')]) !!}
                            {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                        </div>
                    </div>

                    @if(isset($editExpedition->project->workflow_id) && in_array($editExpedition->project->workflow_id, Config::get('config.nfnWorkflows'), false))
                        <div class="form-group {{ ($errors->has('workflow')) ? 'has-error' : '' }}">
                            {!! Form::label('workflow', trans('forms.nfn_workflow_id'), ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-4">
                                {!! Form::text('workflow', isset($editExpedition->nfnWorkflow->workflow) ? $editExpedition->nfnWorkflow->workflow : null, ['class' => 'form-control', 'placeholder' => trans('forms.nfn_workflow_id_note')]) !!}
                                {{ ($errors->has('workflow') ? $errors->first('workflow') : '') }}
                            </div>
                        </div>
                        @if(isset($editExpedition->nfnWorkflow->workflow))
                            <input type="hidden" name="current_workflow"
                                   value="{{ $editExpedition->nfnWorkflow->workflow }}">
                        @endif
                    @endif
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::hidden('id', isset($editExpedition->id) ? $editExpedition->id : null) !!}
                            {!! Form::hidden('admin') !!}
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary pull-right']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <!-- /.box -->
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
</div>