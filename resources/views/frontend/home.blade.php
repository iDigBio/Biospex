@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.home')}}
@stop

{{-- Content --}}
@section('homepage')

<div id="splash">
    <div class="container">
        <img src="/img/logo.png" alt="biospex"/>

        <h1>Use BIOSPEX to provision, advertise, and lead<br/>public Biodiversity Specimen Digitization Expeditions</h1>
    </div>
</div>

<div id="x">
    <!-- Container -->
    <div class="container">
        <!-- Notifications -->
        @include('frontend.layouts.notifications')
        <!-- ./ notifications -->

        <!-- Content -->
        <div class="row">
            <div class="col-md-6">
                <h2>Liberate data from the cabinets</h2>

                <p>The world's 3 billion biodiversity research specimens provide the historical baseline for
                understanding the patterns of Life's diversity and distribution today and projecting future
                changes to it.  But information about the majority of these specimens languishes in cabinets.
                Biospex is a basecamp for launching, advertising, and managing targeted efforts to digitize these
                specimens.  We recognize that motivations to digitize the data can vary a lot, from the museum
                curator to the descendants of a collector reconstructing their ancestor's steps to an environmental
                group interested in the health of a local water body.  Biospex enables each of these to package
                their projects in one or a series of digitization expeditions, launch the expeditions at crowdsourcing
                tools, and widely recruit others to participate.  In the end, you can download the new data AND the
                data goes back to the museum that curates the physical specimen.</p>

                <p>Get started -> <a href="/login">Lead an Expedition</a></p>
            </div>
        </div>


        <h2 class="mailchimp">Subscribe to our mailing list</h2>
        <!-- Begin MailChimp Signup Form -->
        <div id="mc_embed_signup" style="padding: 5px 5px 5px 0px; ">
            <p style="font-size: 11px; font-style: italic;"><span class="asterisk">*</span> indicates required</p>

            <form
                action="http://idigbio.us4.list-manage.com/subscribe/post?u=5c564b4cf1e8157b450723e1c&amp;id=5aa1451449"
                method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
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
                <br/><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button btn btn-xs btn-info">
            </form>

            <br clear="right"/>&nbsp;
        </div>

        <!--End mc_embed_signup-->
        <!-- ./ content -->
    </div>
</div>

@stop