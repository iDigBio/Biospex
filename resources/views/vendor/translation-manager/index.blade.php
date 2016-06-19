<div style="width: 80%; margin: auto;">
    <h1>Translation Manager</h1>
    <p>Warning, translations are not visible until they are exported back to the app/lang file, using 'php artisan translation:export' command or publish button.</p>
    <div class="alert alert-success success-import" style="display:none;">
        <p>Done importing, processed <strong class="counter">N</strong> items! Reload this page to refresh the groups!</p>
    </div>
    <div class="alert alert-success success-find" style="display:none;">
        <p>Done searching for translations, found <strong class="counter">N</strong> items!</p>
    </div>
    <div class="alert alert-success success-publish" style="display:none;">
        <p>Done publishing the translations for group '{{ $group }}'!</p>
    </div>
    @if(Session::has('successPublish'))
        <div class="alert alert-info">
            {{ Session::get('successPublish') }}
        </div>
    @endif
    <p>
        @if(!isset($group))
        <form class="form-inline form-import" method="POST" action="{{ route('admin.translations.import') }}" data-remote="true" role="form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <select name="replace" class="form-control">
                <option value="0">Append new translations</option>
                <option value="1">Replace existing translations</option>
            </select>
            <button type="submit" class="btn btn-success"  data-disable-with="Loading..">Import groups</button>
        </form>
        <form class="form-inline form-find" method="POST" action="{{ route('admin.translations.find') }}" data-remote="true" role="form" data-confirm="Are you sure you want to scan you app folder? All found translation keys will be added to the database.">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <p></p>
            <button type="submit" class="btn btn-info" data-disable-with="Searching.." >Find translations in files</button>
        </form>
        @endif
        @if(isset($group))
            <form class="form-inline form-publish" method="POST" action="{{ route('admin.translations.publish', [$group]) }}" data-remote="true" role="form" data-confirm="Are you sure you want to publish the translations group '{{  $group }}? This will overwrite existing language files.">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <button type="submit" class="btn btn-info" data-disable-with="Publishing.." >Publish translations</button>
                <a href="{{ route('admin.translations.index') }}" class="btn btn-default">Back</a>
            </form>
        @endif
    </p>
    <form role="form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <select name="group" id="group" class="form-control group-select">
                @foreach($groups as $key => $value)
                    <option data-route="{{ $key === '' ? route('admin.translations.index') : route('admin.translations.view') }}" value="{{  $key }}"{{  $key == $group ? ' selected':'' }}>{{  $value }}</option>
                @endforeach
            </select>
        </div>
    </form>
    @if($group)
        <form action="{{ route('admin.translations.add', [$group]) }}" method="POST"  role="form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <textarea class="form-control" rows="3" name="keys" placeholder="Add 1 key per line, without the group prefix"></textarea>
            <p></p>
            <input type="submit" value="Add keys" class="btn btn-primary">
        </form>
        <hr>
    <h4>Total: {{  $numTranslations }}, changed: {{  $numChanged }}</h4>
    <table class="table">
        <thead>
        <tr>
            <th width="15%">Key</th>
            @foreach($locales as $locale)
                <th>{{  $locale }}</th>
            @endforeach
            @if($deleteEnabled)
                <th>&nbsp;</th>
            @endif
        </tr>
        </thead>
        <tbody>

        @foreach($translations as $key => $translation)
            <tr id="{{  $key }}">
                <td>{{  $key }}</td>
                @foreach($locales as $locale)
                    <?php $t = isset($translation[$locale]) ? $translation[$locale] : null ?>

                    <td>
                        <a href="#edit" class="editable status-{{  $t ? $t->status : 0 }} locale-{{  $locale }}" data-locale="{{  $locale }}" data-name="{{  $locale . "|" . $key }}" id="username" data-type="textarea" data-pk="{{  $t ? $t->id : 0 }}" data-url="{{  $editUrl }}" data-title="Enter translation">{{  $t ? htmlentities($t->value, ENT_QUOTES, 'UTF-8', false) : '' }}</a>
                    </td>
                @endforeach
                @if($deleteEnabled)
                    <td>

                        {!! Html::linkWithIcon(route('admin.translations.delete', [$group, $key]), '<span class="glyphicon glyphicon-trash"></span>', ['class' => 'delete-key', 'data-confirm' => 'Are you sure you want to delete the translations for '.  $key . '?']) !!}
                    </td>
                @endif
            </tr>
        @endforeach

        </tbody>
    </table>
    @else
        <p>Choose a group to display the group translations. If no groups are visible, make sure you have run the migrations and imported the translations.</p>

    @endif
</div>