@extends('admin.layout.popup')
{{-- Web site Title --}}
@section('title')
    {{ __('Reconcile') }}
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
                  action="{{ route('admin.reconciles.update', [$projectId, $expeditionId]) }}" role="form">
                {!! method_field('put') !!}
                @csrf
                <input type="hidden" name="_id" value="{{ $reconciles->first()->_id }}">
                <div class="row">
                    <h2 id="output"></h2>
                </div>
                @foreach($data[$reconciles->first()->subject_id] as $column)
                    <div class="row">
                        <div class="input-group mt-5">
                            <div class="col-7">
                                <label for="{{ $column }}" class="col-form-label">{{ __('pages.expert_opinion') }}
                                    <br> {{ $column }}:</label>
                                <textarea class="form-control" rows="3"
                                          id="{{ $column }}"
                                          name="{{ $column }}">{{ $reconciles->first()->{$column} }}</textarea>
                            </div>
                            <div class="col-5">
                                <label class="col-form-label">{{ __('pages.participants_entered') }} {{ $column }}
                                    :</label>
                                @foreach($reconciles->first()->transcriptions->sortByDesc($column) as $transcription)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="{{ $column }}_radio"
                                               id="{{ $transcription->_id }}" value="{{ $transcription->{$column} }}">
                                        <label class="form-check-label" for="{{ $transcription->_id }}">
                                            {!! $transcription->{$column} ?: '<i>participant left blank</i>' !!}
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
                        <a href="{{ route('admin.reconciles.publish', [$projectId, $expeditionId]) }}"
                           class="btn btn-primary p-2 m-1 prevent-default text-uppercase"
                           data-method="post"
                           data-confirm="confirmation"
                           data-title="Publish Reconciled File"
                           data-content="This will publish a new reconciled.csv file containing your edits in the Expedition downloads section.">
                            {{__('pages.publish_reconciled')}}</a>
                    </div>
                </div>
            @else
                <div class="row mt-5">
                    <div class="col-12 m-auto justify-content-center text-center">
                        <p>{{ __('pages.reconciled_message') }}</p>
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
    <script src="{{ secure_asset('admin/js/jquery.panzoom.min.js') }}"></script>
    <script src="{{ secure_asset('admin/js/jquery.form.min.js') }}"></script>
    <script>
        $(function () {
            let $panzoom = $('.panzoom').panzoom();
            $panzoom.parent().on('mousewheel.focal', function (e) {
                e.preventDefault();
                let delta = e.delta || e.originalEvent.wheelDelta;
                let zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
                $panzoom.panzoom('zoom', zoomOut, {
                    animate: false,
                    focal: e
                });
            });

            let options = {
                beforeSubmit: function () {
                    $('#output').removeClass().html('<div class="loader mx-auto"></div>');
                },
                success: function (response) {
                    let css = response.result === 'false' ? 'alert-danger' : 'alert-success';
                    $('#output').addClass(css).html(response.message);
                }
            };

            $('#frmReconcile').ajaxForm(options);
            $(':radio').on('click', function () {
                let id = $(this).attr('name').replace('_radio', '');
                $('#' + id).val($(this).val());
            });

            $('img.lazy').one('load', function () {
                $('.loader').remove();
            }).each(function () {
                if (this.complete) {
                    $(this).trigger('load');
                }
            });
        });

    </script>
@endsection