<tr>
    <td><a href="{{ route('admin.groups.deleteForm', [$group->id, $form->id]) }}'" class="prevent-default"
                   title="{{ t('Delete GeoLocate Form') }}"
                   data-hover="tooltip"
                   data-method="delete"
                   data-confirm="confirmation"
                   data-title="{{ t('Delete GeoLocate Form') }} ?" data-content="{{ t('This will permanently delete the form') }}">
            <i class="fas fa-trash-alt"></i></a>
    </td>
    <td>{{ $form->name }}</td>
    <td>{{ $form->expeditions_count }}</td>
</tr>
