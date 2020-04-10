<div class="col-md-10 offset-2 mx-auto">
    <h3 class="text-center pt-4">{{ __('pages.bingo') }} {{ __('pages.words') }}</h3>
    <hr>
    <table id="words-tbl" class="table table-striped table-bordered dt-responsive nowrap"
           style="width:100%; font-size: .8rem">
        <tbody>
        @foreach($words as $chunk)
            <tr>
                @foreach($chunk as $word)
                    <td>{{ $word->word }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
