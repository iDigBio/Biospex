<div class="form-group">
    <h4>@lang('pages.' . $key)</h4>
    @foreach ($permission as $perm)
        <?php
        if (! array_key_exists($perm['name'], $groupPermissions)) {
            $groupPermissions[$perm['name']] = 0;
        }
        ?>
        <div class="checkbox">
            <label class="checkbox">
                {{ Form::checkbox($perm['name'], 1, $groupPermissions[$perm['name']]) }} @lang($perm['description'])
            </label>
        </div>
    @endforeach
</div>