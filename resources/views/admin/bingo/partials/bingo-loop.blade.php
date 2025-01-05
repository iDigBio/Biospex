<div class="col-md-4 mb-4">
    <div class="card px-4 box-shadow h-100">
        <div class="card-body text-center">
            <h4 class="text-center pt-4">{{ $bingo->title }}</h4>
            <h5 class="text-center color-action">
                {{ $bingo->present()->create_date_to_string }}
                {{ t('for') }}<br>
                {{ $bingo->project->title }}
            </h5>
            <a href="{{ route('front.bingos.join', [$bingo]) }}"
               onclick="return !window.open(this.href, 'Biospex_Bingo_Card', 'width=700,height=800')"
               target="_blank"
               class="btn btn-primary my-4 ml-2 text-uppercase">{{ t('Generate Card') }}</a>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                {!! $bingo->project->present()->project_page_icon !!}
                {!! $bingo->present()->admin_show_icon !!}
                @can('isOwner', $bingo)
                    {!! $bingo->present()->edit_icon !!}
                    {!! $bingo->present()->delete_icon !!}
                @endcan
            </div>
        </div>
    </div>
</div>
