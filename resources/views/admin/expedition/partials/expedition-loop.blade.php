<div class="mx-auto mb-4">
    <div class="card black box-shadow h-100">
        <div class="card-top m-0 p-0">
            <img class="card-img-top" src="{{ $expedition->present()->show_medium_logo }}" alt="Card image cap">
            <div class="card-img-overlay">
                <h2 class="card-title text-center pt-3">{{ $expedition->title }}</h2>
                <i class="card-info fas fa-info-circle float-right"></i>
                <p>{{ $expedition->description }}</p>
            </div>
        </div>

        <div class="card-body card-body-img-top white text-center">
            <div class="d-flex justify-content-between">
                <p class="mr-3"><small>{{ $expedition->stat->local_transcriptions_completed }} {{ t('Digitizations') }}</small></p>
                <p><small>{{ $expedition->stat->percent_completed }}% {{ t('Completed') }}</small></p>
            </div>
            @include('admin.expedition.partials.expedition-tile-warning')
            <hr class="mt-0">
            <div class="d-flex align-items-start justify-content-between mt-4 mx-auto">
                {!! $expedition->present()->expedition_show_icon !!}
                {!! $expedition->present()->expedition_edit_icon !!}
                {!! $expedition->present()->expedition_clone_icon !!}
                @can('isOwner', $expedition->project->group)
                    {!! $expedition->present()->expedition_delete_icon !!}
                @endcan
            </div>
        </div>
    </div>
</div>