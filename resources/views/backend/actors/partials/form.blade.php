<div class="row">
    <!-- right column -->
    <div class="col-md-12">
        <div class="box box-primary {!! Html::collapse(['admin.actors.edit', 'admin.actors.create']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($actor->id) ? 'Edit Actor' : 'Create Actor' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.actors.edit', 'admin.actors.create'], ['fa-minus', 'fa-plus']) !!}"></i>
                    </button>
                </h3>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    @if ($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="red">{{$error}}</div>
                        @endforeach
                    @endif
                    <!-- /.box-header -->
                    <!-- form start -->
                    {!! Form::open([
                        'route' => isset($actor->id) ? ['admin.actors.update', $actor->id] : ['admin.actors.store'],
                        'method' => isset($actor->id) ? 'put' : 'post',
                        'role' => 'form',
                        'class' => 'form-horizontal'
                    ]) !!}
                    <div class="box-body">
                        <div class="form-group required" for="title">
                            {!! Form::label('title', 'Title', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('title', isset($actor->title) ? $actor->title : null, ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                                {{ ($errors->has('title') ? $errors->first('title') : '') }}
                            </div>
                        </div>
                        <div class="form-group required" for="url">
                            {!! Form::label('url', 'Url', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('url', isset($actor->url) ? $actor->url : null, ['class' => 'form-control', 'placeholder' => 'Url']) !!}
                                {{ ($errors->has('url') ? $errors->first('url') : '') }}
                            </div>
                        </div>
                        <div class="form-group required" for="class">
                            {!! Form::label('class', 'Class Name', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('class', isset($actor->class) ? $actor->class : null, ['class' => 'form-control', 'placeholder' => 'Class Name']) !!}
                                {{ ($errors->has('class') ? $errors->first('class') : '') }}
                            </div>
                        </div>
                        <div class="form-group" for="emails">
                            {!! Form::label('emails', 'Coordinator Email', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                @if(isset($actor->contacts))
                                    @foreach($actor->contacts as $key => $contact)
                                        <div class="control-group input-group copyHtml">
                                            <div class="input-group">
                                            <span class="input-group-btn">
                                                <button class="btn btn-danger remove" type="button"><i
                                                            class="fa fa-minus"></i></button>
                                            </span>
                                                {!! Form::text('contacts['.$key.'][email]', $contact->email, ['class' => 'form-control', 'placeholder' => 'Coordinator Email']) !!}
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="control-group input-group after-add-more copyHtml">
                                    <div class="input-group">
                                <span class="input-group-btn">
                                    <button class="btn btn-success add-more" type="button"><i
                                                class="fa fa-plus"></i></button>
                                </span>
                                        {!! Form::text('contacts[][email]', null, ['class' => 'form-control', 'placeholder' => 'Enter Notification Email']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" for="private">
                            {!! Form::label('private', 'Private', ['class' => 'col-sm-2 control-label']) !!}
                            <div class="col-sm-10">
                                {!! Form::checkbox('private', '1', isset($actor->private) ? $actor->private : 0, ['class' => 'minimal']) !!}
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        {{ Form::submit('Submit', ['class' => 'btn btn-primary pull-right']) }}
                    </div>
                    <!-- /.box-footer -->
                    </form>
                    <div class="copy hide">
                        <div class="control-group input-group copyHtml">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <button class="btn btn-danger remove" type="button"><i
                                                class="fa fa-minus"></i></button>
                                </span>
                                {!! Form::text('contacts[][email]', null, ['class' => 'form-control', 'placeholder' => 'Enter Notification Email']) !!}
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.box -->
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!--/.col (right) -->
</div>