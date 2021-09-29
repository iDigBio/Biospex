@extends('admin.layout.popup')
{{-- Web site Title --}}
@section('title')
    {{ t('Expert Review') }}
@stop

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endpush

@section('content')

    <div class="row mt-5 justify-content-center">
        {{ $reconciles->links() }}
        <select name="pagination" id="pagination" class="form-control custom-select ml-2" style="width: 100px"
                required>
            @foreach($reconciles->getUrlRange(1, $reconciles->total()) as $page => $url)
                <option value="{{ $url }}" {{ $reconciles->url($reconciles->currentPage()) === $url ? ' selected=selected' : '' }}>{{ $page }}</option>
            @endforeach
        </select>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="buttons text-center">
                <button id="btnZoomIn" class="btn btn-primary p-2 m-1">Zoom in</button>
                <button id="btnZoomOut" class="btn btn-primary p-2 m-1">Zoom out</button>
                <button id="btnZoomReset" class="btn btn-primary p-2 m-1">Reset</button>
                <label>(or Mouse Wheel)</label>
            </div>
            <div>
                <div id="panzoom">
                    <div class="loader mx-auto"></div>
                    <img src="{{ $imgUrl }}" class="img-fluid lazy">
                </div>
            </div>
        </div>
        <div class="col-6">
            <form method="post" id="frmReconcile"
                  action="{{ route('admin.reconciles.update', [$expedition->id]) }}" role="form">
                {!! method_field('put') !!}
                @csrf
                <input type="hidden" name="_id" value="{{ $reconciles->first()->_id }}">
                <input type="hidden" name="page"
                       value="{{ $reconciles->hasMorePages() ? $reconciles->nextPageUrl() : $reconciles->url($reconciles->currentPage()) }}">
                <div class="row">
                    <h2 id="output"></h2>
                </div>
                @foreach($columns as $mask => $column)
                    <div class="row">
                        <div class="input-group mt-5">
                            <div class="col-7">
                                <label for="{{ $mask }}" class="col-form-label">{{ t('Your expert opinion of') }}
                                    <br> {{ $column }}:</label>
                                <textarea class="form-control" rows="3"
                                          id="{{ $mask }}"
                                          name="{{ $mask }}">{{ $reconciles->first()->{$column} }}</textarea>
                            </div>
                            <div class="col-5">
                                <label class="col-form-label">{{ t('Participants entered for') }} {{ $column }}
                                    :</label>
                                @foreach($reconciles->first()->transcriptions as $transcription)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="radio"
                                               data-column="{{ $mask }}"
                                               value="{{ $transcription->{$column} }}">
                                        <label class="form-check-label" for="{{ $transcription->_id }}">
                                            {!! $transcription->{$column} ?: '<i>'.t('participant left blank').'</i>' !!}
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
                        <button type="submit"
                                class="btn btn-primary text-uppercase mt-5">{{ t('Save') }}</button>
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
                           data-title="{{ t('Publish Reconciled File') }}"
                           data-content="{{ t('This will publish a new reconciled_with_expert_opinion.csv file containing your edits in the Expedition downloads section.') }}">
                            {{t('Publish Expert Review')}}</a>
                    </div>
                </div>
            @else
                <div class="row mt-5">
                    <div class="col-12 m-auto justify-content-center text-center">
                        <p>{{ t('Once you have submitted your expert opinion for each page, go to the last page and click "Publish Reconciled."') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="row mt-5 justify-content-center">
        {{ $reconciles->links() }}
        <select name="pagination" id="pagination" class="form-control custom-select ml-2" style="width: 100px"
                required>
            @foreach($reconciles->getUrlRange(1, $reconciles->total()) as $page => $url)
                <option value="{{ $url }}" {{ $reconciles->url($reconciles->currentPage()) === $url ? ' selected=selected' : '' }}>{{ $page }}</option>
            @endforeach
        </select>
    </div>
@stop

@push('scripts')
    <script src="{{ secure_asset('js/jquery.form.min.js') }}"></script>
    <script src="{{ secure_asset('js/expertReview.min.js') }}"></script>
@endpush