<div class="entry col-md-12">
    <div class="col-md-6 top10">
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
        {!! Form::text('teams[0][title]', null, ['class' => 'form-control', 'placeholder' => trans('pages.event_teams_title')]) !!}
        </div>
        {!! Form::hidden('teams[0][id]', old('teams.[0].id')) !!}
    </div>
</div>