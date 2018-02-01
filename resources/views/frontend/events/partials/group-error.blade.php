<div class="entry">
    <div class="col-sm-6 top10 {!! $errors->has('groups.'.  $i  . '.title') ? 'has-error' : '' !!}">
        {!! $errors->has('groups.'.  $i  . '.title') ? '&nbsp;' . $errors->first('groups.'.  $i  . '.title') : '' !!}
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
            {!! Form::text('groups[' . $i . '][title]', old('groups.[' . $i . '].title'), ['class' => 'form-control', 'placeholder' => trans('forms.event_groups_title')]) !!}
        </div>
    </div>
    {!! Form::hidden('groups['. $i .'][id]', old('groups.[' . $i . '].id')) !!}
</div>
