<div class="container" id="footer-home">
    <!-- Footer -->
    <ul id="social-list">
        <li><a href="https://www.facebook.com/biospex" target="_blank"><img alt="Like iDigBio on Facebook"
                                                                            src="{{ asset('images/facebook.png') }}"/></a></li>
        <li><a href="https://twitter.com/biospex" target="_blank"><img alt="Follow iDigbio on Twitter"
                                                                       src="{{ asset('images/twitter.png') }}"/></a></li>
    </ul>


    <ul id="logo-list">
        <li><a href="http://idigbio.org"><img alt="iDigBio logo" class="logo-center" src="{{ asset('images/idigbio.png') }}"
                                              style="height: 60px; "/></a></li>
        <li><a href="http://ufl.edu"><img alt="University of Florida logo" class="logo-center" src="{{ asset('images/uf.png') }}"
                                          style="width: 60px; height: 60px; "/></a></li>
        <li><a href="http://fsu.edu"><img alt="Florida State University logo" class="logo-center"
                                          src="{{ asset('images/fsu.png') }}" style="width: 60px; height: 60px; "/></a></li>
        <li><a href="http://flmnh.ufl.edu"><img alt="Florida Museum logo" class="logo-center" src="{{ asset('images/flmnh.png') }}"
                                                style="width: 60px; height: 60px; "/></a></li>
        <li><a href="http://nsf.gov"><img alt="National Science Foundation logo" class="logo-center"
                                          src="{{ asset('images/nsf.png') }}" style="width: 60px; height: 60px; "/></a></li>
    </ul>

    <p class="small">{!! trans('html.footer-text') !!}</p>
    <p class="text-center">{{ link_to_route('api.get.index', 'Biospex API') }}</p>
    <!-- ./ footer -->
</div>