<div class="col-md-10 offset-2 mx-auto">
    <h3 class="text-center pt-4">{{ __('Teams Summary') }}</h3>
    <hr>
    @if($event->teams->isEmpty())
        <p class="text-center">{{ __('No teams exist at this time.') }}</p>
    @else
        <div class="color-action text-center">{{ __('Use shift + click to multi-sort') }}</div>
        <table id="teams-tbl" class="table table-striped table-bordered dt-responsive nowrap"
               style="width:100%; font-size: .8rem">
            <thead>
            <tr>
                <th>{{ __('Teams') }}</th>
                <th>{{ __('Users') }}</th>
                <th>{{ __('Transcriptions') }}</th>
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
