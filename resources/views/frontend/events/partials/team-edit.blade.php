<div class="entry col-md-12">
    <div class="col-sm-6 top10">
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
            {!! Form::text('teams[' . $key . '][title]', $team->title, [
                'class' => 'form-control',
                'placeholder' => trans('pages.event_teams_title'),
                'data-type' => 'teams'
                ]) !!}
        </div>
        {!! Form::hidden('teams['. $key .'][id]', $team->id) !!}
    </div>
</div>