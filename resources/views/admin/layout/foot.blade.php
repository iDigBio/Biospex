<!-- Footer -->
<footer id="footer" class="page-footer font-small blue-grey lighten-5">
    <!-- Copyright -->
    <div class="footer-copyright text-center text-black-50 py-3">{{ __('pages.copyright') }}
        <a class="dark-grey-text" href="#"> {{ __('pages.copyright_tag') }}</a>
    </div>
    <!-- Copyright -->
</footer>
@include('common.php-vars-javascript')
<script src="{{ mix('/js/manifest.js', 'admin') }}"></script>
<script src="{{ mix('/js/vendor.js', 'admin') }}"></script>
<script src="{{ mix('/js/admin.js', 'admin') }}"></script>
@yield('custom-script')