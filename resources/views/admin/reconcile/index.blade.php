@extends('admin.layout.popup')
{{-- Web site Title --}}
@section('title')
    {{ __('pages.expert_review') }}
@stop

@section('content')

    <div class="row mt-5 justify-content-center">
        {{ $reconciles->links() }}
    </div>
    <div class="row">
        <div class="col-6">
            <div class="panzoom">
                <div class="loader mx-auto"></div>
                <img src="{{ $imgUrl }}" class="img-fluid lazy">
            </div>
        </div>
        <div class="col-6">
            <form method="post" id="frmReconcile"
                  action="{{ route('admin.reconciles.update', [$expedition->id]) }}" role="form">
                {!! method_field('put') !!}
                @csrf
                <input type="hidden" name="_id" value="{{ $reconciles->first()->_id }}">
                <div class="row">
                    <h2 id="output"></h2>
                </div>
                @foreach($columns as $mask => $column)
                    <div class="row">
                        <div class="input-group mt-5">
                            <div class="col-7">
                                <label for="{{ $mask }}" class="col-form-label">{{ __('pages.expert_review_opinion') }}
                                    <br> {{ $column }}:</label>
                                <textarea class="form-control" rows="3"
                                          id="{{ $mask }}"
                                          name="{{ $mask }}">{{ $reconciles->first()->{$column} }}</textarea>
                            </div>
                            <div class="col-5">
                                <label class="col-form-label">{{ __('pages.expert_review_participants_entered') }} {{ $column }}
                                    :</label>
                                @foreach($reconciles->first()->transcriptions as $transcription)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="radio"
                                               data-column="{{ $mask }}"
                                               id="{{ $transcription->_id }}" value="{{ $transcription->{$column} }}">
                                        <label class="form-check-label" for="{{ $transcription->_id }}">
                                            {!! $transcription->{$column} ?: __('pages.expert_review_blank') !!}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <hr class="header mx-auto mt-5" style="width:300px;">
                    </div>
                @endforeach
                <div class="row">
                    <div class="form-group m-auto">
                        <a href="{{ $reconciles->previousPageUrl() }}" class="btn btn-primary text-uppercase mt-5">
                            {{__('pages.previous')}}</a>
                        <button type="submit"
                                class="btn btn-primary text-uppercase mt-5">{{ __('pages.save') }}</button>
                        <a href="{{ $reconciles->nextPageUrl() }}" class="btn btn-primary text-uppercase mt-5">
                            {{__('pages.next')}}</a>
                    </div>
                </div>
            </form>
            @if(!$reconciles->hasMorePages())
                <div class="row mt-5">
                    <div class="col-12 m-auto justify-content-center text-center">
                        <a href="{{ route('admin.reconciles.publish', [$expedition->project_id, $expedition->id]) }}"
                           class="btn btn-primary p-2 m-1 prevent-default text-uppercase"
                           data-method="post"
                           data-confirm="confirmation"
                           data-title="{{ __('pages.expert_review_pub_data_title') }}"
                           data-content="{{ __('pages.expert_review_pub_data_content') }}">
                            {{__('pages.expert_review_pub_btn')}}</a>
                    </div>
                </div>
            @else
                <div class="row mt-5">
                    <div class="col-12 m-auto justify-content-center text-center">
                        <p>{{ __('pages.expert_review_pub_message') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="row mt-5 justify-content-center">
        {{ $reconciles->links() }}
    </div>
@stop

@section('custom-script')
    <script src="{{ secure_asset('backend/js/jquery.panzoom.min.js') }}"></script>
    <script src="{{ secure_asset('backend/js/jquery.form.min.js') }}"></script>
    <script src="{{ secure_asset('backend/js/expertReview.min.js') }}"></script>
@endsection