<div class="col-md-10 offset-2 mx-auto">
    <h3 class="text-center pt-4">{{ __('pages.bingo') }} {{ __('pages.words') }}</h3>
    <hr>
    <table id="words-tbl" class="table table-striped table-bordered dt-responsive nowrap"
           style="width:100%; font-size: .8rem">
        <thead>
        <tr>
            <th>{{ __('pages.words') }}</th>
            <th>{{ __('pages.definitions') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bingo->words as $word)
            <tr>
                <td>{{ $word->word }}</td>
                <td>{{ $word->definition }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
