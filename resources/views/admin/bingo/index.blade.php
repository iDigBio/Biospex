@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Bingo') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ t('Biospex Bingo') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-sm-8 offset-md-2 text-center">
                <a href="{{ route('admin.bingos.create') }}" type="submit"
                   class="btn btn-primary my-4 ml-2 text-uppercase"><i class="fas fa-plus-circle"></i> {{ t('New Bingo') }}</a>
        </div>
    </div>

    <div class="row">
        @if($bingos->isNotEmpty())
            @each('admin.bingo.partials.bingo-loop', $bingos, 'bingo')
        @else
            <h2 class="mx-auto pt-4">{{ t('No Bingo Games exist.') }}</h2>
        @endif
    </div>
@endsection