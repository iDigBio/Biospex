@if (Request::is('*/expeditions/create'))
<div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
    {{ Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::text('title', null, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) }}
    </div>
    {{ ($errors->has('title') ? $errors->first('title') : '') }}
</div>

<div class="form-group {{ ($errors->has('description')) ? 'has-error' : '' }}">
    {{ Form::label('description', trans('forms.description'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::textarea('description', null, array('class' => 'form-control', 'placeholder' => trans('forms.description'))) }}
    </div>
    {{ ($errors->has('description') ? $errors->first('description') : '') }}
</div>

<div class="form-group {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
    {{ Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::text('keywords', null, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) }}
    </div>
    {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
</div>

<div class="form-group {{ ($errors->has('subjects')) ? 'has-error' : '' }}" for="title">
    {{ Form::label('subjects', trans('forms.assign_subjects'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::text('subjects', null, array('class' => 'form-control', 'placeholder' => $subjects . ' ' . trans('forms.unassigned'))) }}
    </div>
    {{ ($errors->has('subjects') ? $errors->first('subjects') : '') }}
</div>
@else
<div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
    {{ Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::text('title', $expedition->title, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) }}
    </div>
    {{ ($errors->has('title') ? $errors->first('title') : '') }}
</div>

<div class="form-group {{ ($errors->has('description')) ? 'has-error' : '' }}">
    {{ Form::label('description', trans('forms.description'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::textarea('description', $expedition->description, array('class' => 'form-control', 'placeholder' => trans('forms.description'))) }}
    </div>
    {{ ($errors->has('description') ? $errors->first('description') : '') }}
</div>

<div class="form-group {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
    {{ Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::text('keywords', $expedition->keywords, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) }}
    </div>
    {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
</div>

<div class="form-group {{ ($errors->has('subjects')) ? 'has-error' : '' }}" for="title">
    {{ Form::label('subjects', trans('forms.assigned_subjects'), array('class' => 'col-sm-2 control-label')) }}
    <div class="col-sm-10">
        {{ Form::text('subjects', $subjects, array('class' => 'form-control', 'placeholder' => $subjects, 'disabled')) }}
    </div>
    {{ ($errors->has('subjects') ? $errors->first('subjects') : '') }}
</div>
@endif