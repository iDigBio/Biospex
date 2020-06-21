<div class="col-md-10 offset-2 mx-auto">
    <h3 class="text-center pt-4">{{ __('pages.teams') }} {{ __('pages.summary') }}</h3>
    <hr>
    @if($event->teams->isEmpty())
        <p class="text-center">{{ __('pages.teams_none') }}</p>
    @else
        <div class="color-action text-center">{{ __('pages.table_sort') }}</div>
        <table id="teams-tbl" class="table table-striped table-bordered dt-responsive nowrap"
               style="width:100%; font-size: .8rem">
            <thead>
            <tr>
                <th>{{ __('pages.teams') }}</th>
                <th>{{ __('pages.users') }}</th>
                <th>{{ __('pages.digitizations') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($event->teams as $team)
                @foreach ($team->users as $user)
                    <tr>
                        <td>{{ $team->title }}</td>
                        <td>{{ $user->nfn_user }}</td>
                        <td>{{ $user->transcriptions_count }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    @endif
</div>
