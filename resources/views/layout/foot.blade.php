<!-- Footer -->
<footer id="footer" class="page-footer font-small blue-grey lighten-5">
    <!-- Copyright -->
    <div class="footer-copyright text-center py-3">{{ t('© 2019 Copyright') }}
        <a class="" href="#"> {{ t('FSU Deptartment of Biological Science') }}</a>
    </div>
    <!-- Copyright -->
</footer>
@include('partials.php-vars-javascript')
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/admin.js') }}"></script>
@yield('custom-script')