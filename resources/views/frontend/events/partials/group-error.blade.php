<div class="entry col-md-12">
    <div class="col-sm-6 top10 {!! $errors->has('groups.'.  $key  . '.title') ? 'has-error' : '' !!}">
        {!! $errors->has('groups.'.  $key  . '.title') ? '&nbsp;' . $errors->first('groups.'.  $key  . '.title') : '' !!}
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
            {!! Form::text('groups[' . $key . '][title]', old('groups.[' . $key . '].title'), [
                'class' => 'form-control',
                'placeholder' =>
                trans('pages.event_groups_title')
                ]) !!}
        </div>
        {!! Form::hidden('groups['. $key .'][id]', old('groups.[' . $key . '].id')) !!}
    </div>
</div>