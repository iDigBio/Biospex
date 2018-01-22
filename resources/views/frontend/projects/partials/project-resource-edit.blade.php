<div class="entry col-sm-12 project-resource">
    <div class="form-group col-sm-3">
        {!! Form::label('', trans('Type'), ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-btn">
                {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
            </span>
            {!! Form::select('resource['.$i.'][type]', $resourcesSelect, null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group col-sm-3 {!! $errors->has('resource.'. $i . '.name') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('Name/URL'), ['class' => 'control-label']) !!}
        {!! $errors->has('resource.'. $i . '.name') ? '&nbsp;' . $errors->first('resource.'. $i . '.name') : '' !!}
        {!! Form::text('resource['.$i.'][name]', null, ['class' => 'form-control', 'placeholder' => trans('forms.project_resources_name')]) !!}
    </div>
    <div class="form-group col-sm-4 {!! $errors->has('resource.'. $i . '.description') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('Description'), ['class' => 'control-label']) !!}
        {!! $errors->has('resource.'. $i . '.description') ? '&nbsp;' . $errors->first('resource.'. $i . '.description') : '' !!}
        {!! Form::text('resource['.$i.'][description]', null, ['class' => 'form-control', 'placeholder' => trans('forms.project_resources_description')]) !!}
    </div>
    <div class="form-group col-sm-2 {!! $errors->has('resource.'. $i . '.download') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('File'), ['class' => 'control-label']) !!}
        {!! $errors->has('resource.'. $i . '.download') ? '&nbsp;' . $errors->first('resource.'. $i . '.download') : '' !!}
        {!! Form::file('resource['.$i.'][download]') !!}
    </div>
</div>