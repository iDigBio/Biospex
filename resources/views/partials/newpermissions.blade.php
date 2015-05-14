<div class="form-group">
    <h4>@lang('pages.' . $key)</h4>
    @foreach ($permission as $perm)
    <div class="checkbox">
        <label class="checkbox">
            {{ Form::checkbox($perm['name'], 1) }} @lang($perm['description'])
        </label>
    </div>
    @endforeach
</div>