<div class="mx-auto mb-4">
    <div class="card box-shadow h-100">
        <img class="card-img-top" src="{{ $expedition->present()->logo_url }}" alt="Card image cap"
             style="border-radius: 10px;">
        <div class="card-img-overlay">
            <h3 class="card-title text-center">{{ $expedition->title }}</h3>
            <p>{{ $expedition->description }}</p>
        </div>

        <div class="card-body text-center" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
            <div class="d-flex justify-content-between">
                <div class="p-2">{{ $expedition->stat->transcriptions_completed }} {{ __('Transcriptions') }}</div>
                <div class="p-2">{{ $expedition->stat->percent_completed }}% {{ __('Complete') }}</div>
            </div>
            <hr>
            <div class="d-flex justify-content-between mt-4 mx-auto">
                {!! $expedition->present()->expedition_show_icon !!}
                {!! $expedition->present()->expedition_download_icon !!}
                {!! $expedition->present()->expedition_ocr_icon !!}
                {!! $expedition->present()->expedition_edit_icon !!}
                {!! $expedition->present()->expedition_clone_icon !!}
                @can('isOwner', $expedition->project->group)
                    {!! $expedition->present()->expedition_delete_icon !!}
                @endcan
            </div>
        </div>
    </div>
</div>