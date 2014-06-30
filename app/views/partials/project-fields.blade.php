@if (Request::is('*/projects/create'))
    <div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
        {{ Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('title', null, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) }}
        </div>
        {{ ($errors->has('title') ? $errors->first('title') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('contact')) ? 'has-error' : '' }}">
        {{ Form::label('contact', trans('forms.contact'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('contact', null, array('class' => 'form-control', 'placeholder' => trans('forms.contact'))) }}
        </div>
        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
        {{ Form::label('contact_email', trans('forms.contact-email'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('contact_email', null, array('class' => 'form-control', 'placeholder' => trans('forms.contact-email'))) }}
        </div>
        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('managed')) ? 'has-error' : '' }}">
        {{ Form::label('managed', trans('forms.managed'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('managed', null, array('class' => 'form-control', 'placeholder' => trans('forms.managed'))) }}
        </div>
        {{ ($errors->has('managed') ? $errors->first('managed') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('description')) ? 'has-error' : '' }}">
        {{ Form::label('description', trans('forms.description'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('description', null, array('class' => 'form-control', 'placeholder' => trans('forms.short-description'))) }}
        </div>
        {{ ($errors->has('description') ? $errors->first('description') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('goal')) ? 'has-error' : '' }}">
        {{ Form::label('goal', trans('forms.goal'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('goal', null, array('class' => 'form-control', 'placeholder' => trans('forms.goal'))) }}
        </div>
        {{ ($errors->has('goal') ? $errors->first('goal') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('circumscription')) ? 'has-error' : '' }}">
        {{ Form::label('circumscription', trans('forms.circumscription'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('circumscription', null, array('class' => 'form-control', 'placeholder' => trans('forms.circumscription'))) }}
        </div>
        {{ ($errors->has('circumscription') ? $errors->first('circumscription') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('strategy')) ? 'has-error' : '' }}">
        {{ Form::label('strategy', trans('forms.strategy-description'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('strategy', null, array('class' => 'form-control', 'placeholder' => trans('forms.strategy-description'))) }}
        </div>
        {{ ($errors->has('strategy') ? $errors->first('strategy') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
        {{ Form::label('incentives', trans('forms.incentives'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('incentives', null, array('class' => 'form-control', 'placeholder' => trans('forms.incentives'))) }}
        </div>
        {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
        {{ Form::label('geographic_scope', trans('forms.geographic-scope'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('geographic_scope', null, array('class' => 'form-control', 'placeholder' => trans('forms.geographic-scope'))) }}
        </div>
        {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
        {{ Form::label('taxonomic_scope', trans('forms.taxonomic-scope'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('taxonomic_scope', null, array('class' => 'form-control', 'placeholder' => trans('forms.taxonomic-scope'))) }}
        </div>
        {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
        {{ Form::label('temporal_scope', trans('forms.temporal-scope'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('temporal_scope', null, array('class' => 'form-control', 'placeholder' => trans('forms.temporal-scope'))) }}
        </div>
        {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
        {{ Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('keywords', null, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) }}
        </div>
        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('hashtag')) ? 'has-error' : '' }}">
        {{ Form::label('hashtag', trans('forms.hashtag'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('hashtag', null, array('class' => 'form-control', 'placeholder' => trans('forms.hashtag'))) }}
        </div>
        {{ ($errors->has('hashtag') ? $errors->first('hashtag') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
        {{ Form::label('activities', trans('forms.activities'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('activities', null, array('class' => 'form-control', 'placeholder' => trans('forms.activities'))) }}
        </div>
        {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
        {{ Form::label('language_skills', trans('forms.language-skills'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('language_skills', null, array('class' => 'form-control', 'placeholder' => trans('forms.language-skills'))) }}
        </div>
        {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('workflow')) ? 'has-error' : '' }}">
        {{ Form::label('workflow', trans('forms.workflow'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::select('workflow', array('1' => 'WorkFlow 1', '2' => 'WorkFlow 2', '3' => 'WorkFlow 3', ), array('class' => 'form-control', 'placeholder' => trans('forms.workflow'))) }}
        </div>
        {{ ($errors->has('workflow') ? $errors->first('workflow') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
        {{ Form::label('logo', trans('forms.logo'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::file('logo') }}
        </div>
        {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
        {{ Form::label('banner', trans('forms.banner'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::file('banner') }}
        </div>
        {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
    </div>
    <?php
    $i = 0;
    ?>
    @while ($i <= $count)
    <div {{ $count == 0 ? 'style="display:none"' : '' }} class="target form-group">
    {{ Form::label('target', trans('forms.target'), array('class' => 'col-sm-2 control-label')) }}
    <div class="panel panel-default col-sm-10">
        <div class="panel-body">
            <div class="form-group col-sm-10 {{ ($errors->has('target_name')) ? 'has-error' : '' }}">
                {{ Form::text('target_core['.$i.']', null, array('class' => 'form-control', 'placeholder' => trans('forms.target-core'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_name')) ? 'has-error' : '' }}">
                {{ Form::text('target_name['.$i.']', null, array('class' => 'form-control', 'placeholder' => trans('forms.target-name'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_description')) ? 'has-error' : '' }}">
                {{ Form::text('target_description['.$i.']', null, array('class' => 'form-control', 'placeholder' => trans('forms.target-description'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_valid_response')) ? 'has-error' : '' }}">
                {{ Form::text('target_valid_response['.$i.']', null, array('class' => 'form-control', 'placeholder' => trans('forms.target-valid-response'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_inference')) ? 'has-error' : '' }}">
                {{ Form::text('target_inference['.$i.']', null, array('class' => 'form-control', 'placeholder' => trans('forms.target-inference'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_inference_example')) ? 'has-error' : '' }}">
                {{ Form::text('target_inference_example['.$i.']', null, array('class' => 'form-control', 'placeholder' => trans('forms.target-inference-example'))) }}
            </div>
        </div>
    </div>
    </div>
    <?php $i++ ?>
    @endwhile
@else
    <div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
        {{ Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('title', $project->title, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) }}
        </div>
        {{ ($errors->has('title') ? $errors->first('title') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('contact')) ? 'has-error' : '' }}">
        {{ Form::label('contact', trans('forms.contact'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('contact', $project->contact, array('class' => 'form-control', 'placeholder' => trans('forms.contact'))) }}
        </div>
        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
        {{ Form::label('contact_email', trans('forms.contact-email'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('contact_email', $project->contact_email, array('class' => 'form-control', 'placeholder' => trans('forms.contact-email'))) }}
        </div>
        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('managed')) ? 'has-error' : '' }}">
        {{ Form::label('managed', trans('forms.managed'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('managed', $project->managed, array('class' => 'form-control', 'placeholder' => trans('forms.managed'))) }}
        </div>
        {{ ($errors->has('managed') ? $errors->first('managed') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('description')) ? 'has-error' : '' }}">
        {{ Form::label('description', trans('forms.description'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('description', $project->description, array('class' => 'form-control', 'placeholder' => trans('forms.short-description'))) }}
        </div>
        {{ ($errors->has('description') ? $errors->first('description') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('goal')) ? 'has-error' : '' }}">
        {{ Form::label('goal', trans('forms.goal'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('goal', $project->goal, array('class' => 'form-control', 'placeholder' => trans('forms.goal'))) }}
        </div>
        {{ ($errors->has('goal') ? $errors->first('goal') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('circumscription')) ? 'has-error' : '' }}">
        {{ Form::label('circumscription', trans('forms.circumscription'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('circumscription', $project->circumscription, array('class' => 'form-control', 'placeholder' => trans('forms.circumscription'))) }}
        </div>
        {{ ($errors->has('circumscription') ? $errors->first('circumscription') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('strategy')) ? 'has-error' : '' }}">
        {{ Form::label('strategy', trans('forms.strategy-description'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('strategy', $project->strategy, array('class' => 'form-control', 'placeholder' => trans('forms.strategy-description'))) }}
        </div>
        {{ ($errors->has('strategy') ? $errors->first('strategy') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
        {{ Form::label('incentives', trans('forms.incentives'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::textarea('incentives', $project->incentives, array('class' => 'form-control', 'placeholder' => trans('forms.incentives'))) }}
        </div>
        {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
        {{ Form::label('geographic_scope', trans('forms.geographic-scope'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('geographic_scope', $project->geographic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.geographic-scope'))) }}
        </div>
        {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
        {{ Form::label('taxonomic_scope', trans('forms.taxonomic-scope'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('taxonomic_scope', $project->taxonomic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.taxonomic-scope'))) }}
        </div>
        {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
        {{ Form::label('temporal_scope', trans('forms.temporal-scope'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('temporal_scope', $project->temporal_scope, array('class' => 'form-control', 'placeholder' => trans('forms.temporal-scope'))) }}
        </div>
        {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
        {{ Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('keywords', $project->keywords, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) }}
        </div>
        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('hashtag')) ? 'has-error' : '' }}">
        {{ Form::label('hashtag', trans('forms.hashtag'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('hashtag', $project->hashtag, array('class' => 'form-control', 'placeholder' => trans('forms.hashtag'))) }}
        </div>
        {{ ($errors->has('hashtag') ? $errors->first('hashtag') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
        {{ Form::label('activities', trans('forms.activities'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('activities', $project->activities, array('class' => 'form-control', 'placeholder' => trans('forms.activities'))) }}
        </div>
        {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
        {{ Form::label('language_skills', trans('forms.language-skills'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::text('language_skills', $project->language_skills, array('class' => 'form-control', 'placeholder' => trans('forms.language-skills'))) }}
        </div>
        {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('workflow')) ? 'has-error' : '' }}">
        {{ Form::label('workflow', trans('forms.workflow'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-4">
            {{ Form::select('workflow', array(1 => 'WorkFlow 1', 2 => 'WorkFlow 2', 3 => 'WorkFlow 3'), $project->workflow, array('class' => 'form-control')) }}
        </div>
        {{ ($errors->has('workflow') ? $errors->first('workflow') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
        {{ Form::label('logo', trans('forms.logo'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::file('logo') }}
        </div>
        {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
        {{ Form::label('banner', trans('forms.banner'), array('class' => 'col-sm-2 control-label')) }}
        <div class="col-sm-10">
            {{ Form::file('banner') }}
        </div>
        {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
    </div>

    <?php
    $i = 0;
    ?>
    @while ($i <= $count)
    <?php
    $target_core = isset($project->target_fields[$i]->target_core) ? $project->target_fields[$i]->target_core : '';
    $target_name = isset($project->target_fields[$i]->target_name) ? $project->target_fields[$i]->target_name : '';
    $target_description = isset($project->target_fields[$i]->target_description) ? $project->target_fields[$i]->target_description : '';
    $target_valid_response = isset($project->target_fields[$i]->target_valid_response) ? $project->target_fields[$i]->target_valid_response : '';
    $target_inference = isset($project->target_fields[$i]->target_inference) ? $project->target_fields[$i]->target_inference : '';
    $target_inference_example = isset($project->target_fields[$i]->target_inference_example) ? $project->target_fields[$i]->target_inference_example : '';
    ?>
    <div {{ $count == 0 ? 'style="display:none"' : '' }} class="target form-group">
    {{ Form::label('target', trans('forms.target'), array('class' => 'col-sm-2 control-label')) }}
    <div class="panel panel-default col-sm-10">
        <div class="panel-body">
            <div class="form-group col-sm-10 {{ ($errors->has('target_core')) ? 'has-error' : '' }}">
                {{ Form::text('target_core['.$i.']', $target_core, array('class' => 'form-control', 'placeholder' => trans('forms.target-core'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_name')) ? 'has-error' : '' }}">
                {{ Form::text('target_name['.$i.']', $target_name, array('class' => 'form-control', 'placeholder' => trans('forms.target-name'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_description')) ? 'has-error' : '' }}">
                {{ Form::text('target_description['.$i.']', $target_description, array('class' => 'form-control', 'placeholder' => trans('forms.target-description'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_valid_response')) ? 'has-error' : '' }}">
                {{ Form::text('target_valid_response['.$i.']', $target_valid_response, array('class' => 'form-control', 'placeholder' => trans('forms.target-valid-response'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_inference')) ? 'has-error' : '' }}">
                {{ Form::text('target_inference['.$i.']', $target_inference, array('class' => 'form-control', 'placeholder' => trans('forms.target-inference'))) }}
            </div>
            <div class="form-group col-sm-10 {{ ($errors->has('target_inference_example')) ? 'has-error' : '' }}">
                {{ Form::text('target_inference_example['.$i.']', $target_inference_example, array('class' => 'form-control', 'placeholder' => trans('forms.target-inference-example'))) }}
            </div>
        </div>
    </div>
    </div>
    <?php $i++ ?>
    @endwhile
@endif