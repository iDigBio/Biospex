<!-- Footer -->
<footer id="footer" class="page-footer font-small blue-grey lighten-5">
    <!-- Copyright -->
    <div class="footer-copyright text-center text-black-50 py-3">{{ __('pages.copyright') }}
        <a class="dark-grey-text" href="#"> {{ __('pages.copyright_tag') }}</a>
    </div>
    <!-- Copyright -->
</footer>
@include('common.php-vars-javascript')
<script src="{{ mix('/js/manifest.js', 'backend') }}"></script>
<script src="{{ mix('/js/vendor.js', 'backend') }}"></script>
<script src="{{ mix('/js/admin.js', 'backend') }}"></script>
@yield('custom-script')