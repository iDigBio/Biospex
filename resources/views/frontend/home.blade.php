@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{trans('pages.home')}}
@stop

{{-- Content --}}
@section('homepage')
    <div id="splash">
        <div class="pull-left">
        <img src="/img/logo.png" alt="biospex"/>
        {!! trans('html.homepage-header') !!}
        </div>
        <!--
        <div id="carousel" class="carousel slide pull-right" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carousel" data-slide-to="0" class="active"></li>
                <li data-target="#carousel" data-slide-to="1"></li>
                <li data-target="#carousel" data-slide-to="2"></li>
                <li data-target="#carousel" data-slide-to="3"></li>
                <li data-target="#carousel" data-slide-to="4"></li>
            </ol>
            <div class="carousel-inner">
                <?php $i = 0 ?>
                @foreach($projects as $project)
                    <div class="item {{ $i===0 ? 'active' : '' }}">
                        <img src="{{ $project->banner->url() }}" alt="{{ $project->title }}"/>
                        <div class="carousel-caption">
                            <h3>{{ $project->title }}</h3>
                        </div>
                    </div>
                    <?php $i++ ?>
                @endforeach
            </div>
            <a class="left carousel-control" href="#carousel" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <a class="right carousel-control" href="#carousel" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div>
        -->
    </div>
    <div class="container-fluid" id="home-content">
        <div class="col-md-11 col-lg-offset-1">
            <!-- Notifications -->
        @include('frontend.layouts.notices')
        <!-- ./ notifications -->

            <!-- Content -->
            <div class="row">
                <div class="col-md-5">
                    {!! trans('html.homepage-text') !!}
                    <h2 class="mailchimp">Subscribe to our mailing list</h2>
                    <!-- Begin MailChimp Signup Form -->
                    <div id="mc_embed_signup" style="padding: 5px 5px 5px 0px; ">
                        <p style="font-size: 11px; font-style: italic;"><span class="asterisk">*</span> indicates
                            required</p>

                        <form
                                action="http://idigbio.us4.list-manage.com/subscribe/post?u=5c564b4cf1e8157b450723e1c&amp;id=5aa1451449"
                                method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form"
                                class="validate"
                                target="_blank" novalidate>

                            <div class="mc-field-group">
                                <label for="mce-EMAIL">Email Address <span class="asterisk">*</span>
                                </label>
                                <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                            </div>
                            <div class="mc-field-group">
                                <label for="mce-FNAME">First Name </label>
                                <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
                            </div>
                            <div class="mc-field-group">
                                <label for="mce-LNAME">Last Name </label>
                                <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
                            </div>
                            <div id="mce-responses" class="clear">
                                <div class="response" id="mce-error-response" style="display:none"></div>
                                <div class="response" id="mce-success-response" style="display:none"></div>
                            </div>
                            <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                            <div style="position: absolute; left: -5000px;">
                                <input type="text" name="b_5c564b4cf1e8157b450723e1c_5aa1451449" tabindex="-1" value="">
                            </div>
                            <br/><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe"
                                        class="button btn btn-xs btn-info">
                        </form>

                        <br clear="right"/>&nbsp;
                    </div>
                    <!--End mc_embed_signup-->
                </div>
                <div class="col-md-2">
                </div>
                <div class="col-md-5">
                    <div class="panel panel-primary" style="margin-top: 50px;">
                        <div class="panel-heading">
                            <h3 class="panel-title">The Buzz</h3>
                        </div>
                        <div class="panel-body">
                            <a class="twitter-timeline" href="https://twitter.com/hashtag/BIOSPEX"
                               data-widget-id="742050505711595520">#BIOSPEX Tweets</a>
                            <script>!function (d, s, id) {
                                    var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                                    if (!d.getElementById(id)) {
                                        js = d.createElement(s);
                                        js.id = id;
                                        js.src = p + "://platform.twitter.com/widgets.js";
                                        fjs.parentNode.insertBefore(js, fjs);
                                    }
                                }(document, "script", "twitter-wjs");</script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop