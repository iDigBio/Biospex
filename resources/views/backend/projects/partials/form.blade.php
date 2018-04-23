<div class="row">
    <!-- right column -->
    <div class="col-md-12">
        <div class="box box-primary {!! Html::collapse(['admin.projects.edit', 'admin.projects.*']) !!} box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">{{ isset($editProject->id) ? 'Edit Project' : 'Create Project' }}
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa {!! Html::setIconByRoute(['admin.projects.edit', 'admin.projects.*'], ['fa-minus', 'fa-plus']) !!}"></i>
                    </button>
                </h3>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="panel-body">
                    {!! Form::open([
                    'route' => isset($editProject->id) ? ['admin.projects.update', $editProject->id] : ['admin.projects.store'],
                    'method' => isset($editProject->id) ? 'put' : 'post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                    ]) !!}
                    <div class="form-group required {{ ($errors->has('group_id')) ? 'has-error' : '' }}" for="group">
                        {!! Form::label('group_id', trans('pages.group'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::select('group_id', $selectGroups, isset($editProject->group_id) ? $editProject->group_id : null, ['class' => 'form-control']) !!}
                        </div>
                        {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('status')) ? 'has-error' : '' }}" for="group">
                        {!! Form::label('status', trans('pages.status'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::select('status', $statusSelect, isset($editProject->status)  ? $editProject->status : null, ['class' => 'form-control']) !!}
                        </div>
                        {{ ($errors->has('status') ? $errors->first('status') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                        {!! Form::label('title', trans('pages.title'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('title', isset($editProject->title) ? $editProject->title : null, array('class' => 'form-control', 'placeholder' => trans('pages.title'))) !!}
                        </div>
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                        {!! Form::label('contact', trans('pages.contact'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('contact', isset($editProject->contact) ? $editProject->contact : null, array('class' => 'form-control', 'placeholder' => trans('pages.contact'))) !!}
                        </div>
                        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                        {!! Form::label('contact_email', trans('pages.contact') . ' ' . trans('pages.email'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('contact_email', isset($editProject->contact_email) ? $editProject->contact_email : null, array('class' => 'form-control', 'placeholder' => trans('pages.contact') . ' ' . trans('pages.email'))) !!}
                        </div>
                        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('contact_title')) ? 'has-error' : '' }}">
                        {!! Form::label('contact_title', trans('pages.contact').' '.trans('pages.title'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('contact_title', isset($editProject->contact_title) ? $editProject->contact_title : null, array('class' => 'form-control', 'placeholder' => trans('pages.contact').' '.trans('pages.title'))) !!}
                        </div>
                        {{ ($errors->has('contact_title') ? $errors->first('contact_title') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
                        {!! Form::label('organization', trans('pages.organization'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('organization', isset($editProject->organization) ? $editProject->organization : null, array('class' => 'form-control', 'placeholder' => trans('pages.organization_format'))) !!}
                        </div>
                        {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
                        {!! Form::label('organization_website', trans('pages.organization_website'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('organization_website', isset($editProject->organization_website) ? $editProject->organization_website : null, array('class' => 'form-control', 'placeholder' => trans('pages.organization_website_format'))) !!}
                        </div>
                        {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
                        {!! Form::label('project_partners', trans('pages.project_partners'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::textarea('project_partners', isset($editProject->project_partners) ? $editProject->project_partners : null, array('class' => 'form-control', 'placeholder' => trans('pages.project_partners'))) !!}
                        </div>
                        {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
                        {!! Form::label('funding_source', trans('pages.funding_source'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::textarea('funding_source', isset($editProject->funding_source) ? $editProject->funding_source : null, array('class' => 'form-control', 'placeholder' => trans('pages.funding_source'))) !!}
                        </div>
                        {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
                        {!! Form::label('description_short', trans('pages.description_short'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('description_short', isset($editProject->description_short) ? $editProject->description_short : null, array('class' => 'form-control', 'placeholder' => trans('pages.description_short_max'))) !!}
                        </div>
                        {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
                        {!! Form::label('description_long', trans('pages.description_long'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::textarea('description_long', isset($editProject->description_long) ? $editProject->description_long : null, array('class' => 'form-control textarea', 'placeholder' => trans('pages.description_long'))) !!}
                        </div>
                        {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
                        {!! Form::label('incentives', trans('pages.incentives'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::textarea('incentives', isset($editProject->incentives) ? $editProject->incentives : null, array('size' => '30x3', 'class' => 'form-control', 'placeholder' => trans('pages.incentives'))) !!}
                        </div>
                        {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
                        {!! Form::label('geographic_scope', trans('pages.geographic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('geographic_scope', isset($editProject->geographic_scope) ? $editProject->geographic_scope : null, array('class' => 'form-control', 'placeholder' => trans('pages.geographic_scope'))) !!}
                        </div>
                        {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
                        {!! Form::label('taxonomic_scope', trans('pages.taxonomic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('taxonomic_scope', isset($editProject->taxonomic_scope) ? $editProject->taxonomic_scope : null, array('class' => 'form-control', 'placeholder' => trans('pages.taxonomic_scope'))) !!}
                        </div>
                        {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
                        {!! Form::label('temporal_scope', trans('pages.temporal_scope'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('temporal_scope', isset($editProject->temporal_scope) ? $editProject->temporal_scope : null, array('class' => 'form-control', 'placeholder' => trans('pages.temporal_scope'))) !!}
                        </div>
                        {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
                    </div>

                    <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                        {!! Form::label('keywords', trans('pages.keywords'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('keywords', isset($editProject->keywords) ? $editProject->keywords : null, array('class' => 'form-control', 'placeholder' => trans('pages.keywords'))) !!}
                        </div>
                        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
                        {!! Form::label('blog_url', trans('pages.blog_url'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('blog_url', isset($editProject->blog_url) ? $editProject->blog_url : null, array('class' => 'form-control', 'placeholder' => trans('pages.blog_url_format'))) !!}
                        </div>
                        {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
                        {!! Form::label('facebook', trans('pages.facebook'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('facebook', isset($editProject->facebook) ? $editProject->facebook : null, array('class' => 'form-control', 'placeholder' => trans('pages.facebook_format'))) !!}
                        </div>
                        {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
                        {!! Form::label('twitter', trans('pages.twitter'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('twitter', isset($editProject->twitter) ? $editProject->twitter : null, array('class' => 'form-control', 'placeholder' => trans('pages.twitter_format'))) !!}
                        </div>
                        {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
                        {!! Form::label('activities', trans('pages.activities'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('activities', isset($editProject->activities) ? $editProject->activities : null, array('class' => 'form-control', 'placeholder' => trans('pages.activities'))) !!}
                        </div>
                        {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
                        {!! Form::label('language_skills', trans('pages.language_skills'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-10">
                            {!! Form::text('language_skills', isset($editProject->language_skills) ? $editProject->language_skills : null, array('class' => 'form-control', 'placeholder' => trans('pages.language_skills'))) !!}
                        </div>
                        {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
                    </div>

                    @if($workflowEmpty)
                        <div class="form-group required {{ ($errors->has('workflow_id')) ? 'has-error' : '' }}">
                            {!! Form::label('workflow_id', trans('pages.workflows'), array('class' => 'col-sm-2 control-label')) !!}
                            <div class="col-sm-4">
                                {!! Form::select('workflow_id', $workflows, isset($editProject->workflow_id) ? $editProject->workflow_id : null, ['class' => 'form-control',]) !!}
                            </div>
                            {{ ($errors->has('workflow_id') ? $errors->first('workflow_id') : '') }}
                        </div>
                    @else
                        <div class="form-group required {{ ($errors->has('workflow_id')) ? 'has-error' : '' }}">
                            {!! Form::label('workflow_id', trans('pages.workflows'), array('class' => 'col-sm-2 control-label')) !!}
                            <div class="col-sm-4">
                                {!! Form::select('workflow_id', $workflows, isset($editProject->workflow_id) ? $editProject->workflow_id : null, ['disabled', 'data-width' => 'fit']) !!}
                            </div>
                            @if( ! $workflowEmpty)
                                {!! Form::hidden('workflow_id', $editProject->workflow_id) !!}
                            @endif
                            {{ ($errors->has('workflow_id') ? $errors->first('workflow_id') : '') }}
                        </div>
                    @endif

                    <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
                        {!! Form::label('logo', trans('pages.logo'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-5">
                            {!! Form::file('logo') !!} {{ trans('pages.logo_max') }}
                        </div>
                        <div class="col-sm-5">
                            <img src="{{ isset($editProject) ? $editProject->logo_thumb_url : null }}"/>
                        </div>
                        {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
                    </div>

                    <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
                        {!! Form::label('banner', trans('pages.banner'), array('class' => 'col-sm-2 control-label')) !!}
                        <div class="col-sm-5">
                            {!! Form::file('banner') !!} {{ trans('pages.banner_min') }}
                        </div>
                        <div class="col-sm-5">
                            <img src="{{ isset($editProject) ? $editProject->banner_thumb_url : null }}"/>
                        </div>
                        {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            {!! Form::hidden('id', isset($editProject) ? $editProject->id : null) !!}
                            {{ Form::submit('Submit', ['class' => 'btn btn-primary pull-right']) }}
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