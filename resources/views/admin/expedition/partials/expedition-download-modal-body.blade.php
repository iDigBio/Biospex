@if($error)
    <div class="col-md-12 text-center">
        <h3>{{ t('You do not have sufficient permissions.') }}</h3>
    </div>
@else
    @foreach ($expedition->actors as $actor)
        <div class="col-md-12 mb-2">
            <div id="downloadsTbl" class="table-responsive">
                <h2 class="color-action">{{ $actor->title }}</h2>
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>{{ t('Download Type') }}</th>
                        <th>{{ t('Filename') }}</th>
                        <th>{{ t('File Size') }}</th>
                        <th>{{ t('Created') }}</th>
                        <th>{{ t('Updated') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        @forelse ($actor->downloads as $download)
                            @include('admin.expedition.partials.expedition-download-loop')
                        @empty
                            <tr><td>{{ t('No downloads exist.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif