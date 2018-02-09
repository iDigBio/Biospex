<div class="entry col-sm-12">
    <div class="col-sm-3 {!! $errors->has('resources.'.  $i  . '.type') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('Type'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'.  $i  . '.type') ? '&nbsp;' . $errors->first('resources.'.  $i  . '.type') : '' !!}
        <div class="input-group">
            <span class="input-group-btn">
                {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
            </span>
            {!! Form::select('resources['. $i .'][type]', $resourcesSelect, old('resources.'. $i .'.type'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-sm-3 {!! $errors->has('resources.'.  $i  . '.name') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('forms.name_url'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'.  $i  . '.name') ? '&nbsp;' . $errors->first('resources.'.  $i  . '.name') : '' !!}
        {!! Form::text('resources['. $i .'][name]', old('resources.'. $i .'.name'), ['class' => 'form-control', 'placeholder' => trans('pages.project_resources_name')]) !!}
    </div>
    <div class="col-sm-4 {!! $errors->has('resources.'.  $i  . '.description') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('Description'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'.  $i  . '.description') ? '&nbsp;' . $errors->first('resources.'.  $i  . '.description') : '' !!}
        {!! Form::text('resources['. $i .'][description]', old('resources'. $i .'description'), ['class' => 'form-control', 'placeholder' => trans('pages.project_resources_description')]) !!}
    </div>
    <div class="col-sm-2 {!! $errors->has('resources.'.  $i  . '.download') ? 'has-error' : '' !!}">
        {!! Form::label('', trans('File'), ['class' => 'control-label']) !!}
        {!! $errors->has('resources.'.  $i  . '.download') ? '&nbsp;' . $errors->first('resources.'.  $i  . '.download') : '' !!}
        {!! Form::file('resources['. $i .'][download]') !!}
        @if(! empty(old('resources.' . $i . '.download_file_name')))
            <div class="col-sm-5 fileName">
                {{ old('resources.' . $i . '.download_file_name') }}
            </div>
            {!! Form::hidden('resources['. $i .'][download_file_name]', old('resources.' . $i . '.download_file_name')) !!}
        @endif
    </div>
    {!! Form::hidden('resources['. $i .'][id]', old('resources'. $i .'id')) !!}
</div>