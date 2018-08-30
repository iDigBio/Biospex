<div class="entry col-md-12">
    <div class="col-sm-6 top10 {!! $errors->has('teams.'.  $i  . '.title') ? 'has-error' : '' !!}">
        {!! $errors->has('teams.'.  $i  . '.title') ? '&nbsp;' . $errors->first('teams.'.  $i  . '.title') : '' !!}
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
            {!! Form::text('teams[' . $i . '][title]', old('teams.[' . $i . '].title'), [
                'class' => 'form-control',
                'placeholder' =>
                trans('pages.event_teams_title')
                ]) !!}
        </div>
        {!! Form::hidden('teams['. $i .'][id]', old('teams.[' . $i . '].id')) !!}
    </div>
</div>