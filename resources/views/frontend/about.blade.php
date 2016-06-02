@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.about')
@stop

{{-- Content --}}
@section('content')
    <div class="row centered-form top-buffer">
        <div class="col-md-8 col-md-offset-2">
            <h3>{{ trans('pages.about') }}</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <p>Biospex about text.... Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio.
                Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet.
                Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum
                lacinia arcu eget nulla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per
                inceptos himenaeos. Curabitur sodales ligula in libero. Sed dignissim lacinia nunc.</p>
        </div>
    </div>

    <div class="row centered-form">
        <div class="col-md-8 col-md-offset-2">
            <h3>{{ trans('pages.team_biospex') }}</h3>
        </div>

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-info flex-col">
                <div class="panel-heading"><h3 class="panel-title">Principle Investigators</h3></div>
                <div class="panel-body flex-grow">
                    <ul class="list-unstyled">
                        <li><strong>Name: </strong>Austin Mast</li>
                        <li><strong>Institution: </strong>Associate Professor, Department of Biological Science,
                            Florida
                            State
                            University
                        </li>
                        <li><strong>Email: {{ Html::mailto('amast@bio.fsu.edu', 'amast@bio.fsu.edu') }}</strong></li>
                        <li>&nbsp;</li>
                        <li><strong>Name: </strong>Greg Ricarrdi</li>
                        <li><strong>Institution: </strong>Director, Institute for Digital Information and
                            Scientific
                            Communication, Florida State University
                        </li>
                        <li><strong>Email: {{ Html::mailto('griccardi@fsu.edu', 'griccardi@fsu.edu') }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row centered-form">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-info flex-col">
                <div class="panel-heading"><h3 class="panel-title">What to Label??</h3></div>
                <div class="panel-body flex-grow">
                    <ul class="list-unstyled">
                        <ul class="list-unstyled">
                            <li><strong>Name: </strong>Libby Ellwood</li>
                            <li><strong>Institution: </strong>Florida State University</li>
                            <li>
                                <strong>Email: </strong>{{ Html::mailto('eellwood@bio.fsu.edu', 'eellwood@bio.fsu.edu') }}
                            </li>
                        </ul>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row centered-form">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-info flex-col">
                <div class="panel-heading"><h3 class="panel-title">Developers</h3></div>
                <div class="panel-body flex-grow">
                    <ul class="list-unstyled">
                        <ul class="list-unstyled">
                            <li><strong>Name: </strong>Robert Bruhn</li>
                            <li><strong>Institution: </strong>Florida State University</li>
                            <li><strong>Email: </strong>{{ Html::mailto('bruhnrp@yahoo.com', 'bruhnrp@yahoo.com') }}
                            </li>
                        </ul>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection