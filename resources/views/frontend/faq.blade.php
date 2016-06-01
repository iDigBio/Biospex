@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.faq')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h3>{{ trans('pages.faq_title') }}</h3>
    </div>
    <div class="col-md-10 col-md-offset-1">
        @if( isset($questions) && ! empty($questions) )
            @foreach($questions as $question)
                <div class="question">
                    <h4>{{ $question['question'] }}</h4>
                </div>

                <div class="answer">
                    <?php echo $question['answer']; ?>
                </div>
            @endforeach
        @endif
    </div>
</div>
@stop