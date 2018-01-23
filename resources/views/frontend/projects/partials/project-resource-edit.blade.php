<div class="entry col-sm-12">
    <div class="col-sm-3">
        {!! Form::label('', trans('Type'), ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-btn">
                {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
            </span>
            {!! Form::select('resources['.$i.'][type]', $resourcesSelect, null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-sm-3 {!! $errors->has('resources.'. $i . '.name') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('Name/URL'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'. $i . '.name') ? '&nbsp;' . $errors->first('resources.'. $i . '.name') : '' !!}
        {!! Form::text('resources['.$i.'][name]', null, ['class' => 'form-control', 'placeholder' => trans('forms.project_resources_name')]) !!}
    </div>
    <div class="col-sm-4 {!! $errors->has('resources.'. $i . '.description') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('Description'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'. $i . '.description') ? '&nbsp;' . $errors->first('resources.'. $i . '.description') : '' !!}
        {!! Form::text('resources['.$i.'][description]', null, ['class' => 'form-control', 'placeholder' => trans('forms.project_resources_description')]) !!}
    </div>
    <div class="col-sm-2 {!! $errors->has('resources.'. $i . '.download') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('File'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'. $i . '.download') ? '&nbsp;' . $errors->first('resources.'. $i . '.download') : '' !!}
        {!! Form::file('resources['.$i.'][download]') !!}
    </div>
</div>