@if($groups->isNotEmpty())
    @each('admin.group.partials.group-loop', $groups, 'group')
@else
    <h2 class="mx-auto pt-4">{{ __('No Groups exist.') }}</h2>
@endif