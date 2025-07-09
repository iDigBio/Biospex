<!-- Footer -->
<footer id="footer" class="page-footer font-small blue-grey lighten-5">
    <!-- Copyright -->
    <div class="footer-copyright text-center text-black-50 py-3">{{ t('© 2014–%s Copyright', \Carbon\Carbon::now()->year) }}
        <a class="dark-grey-text" href="#"> {{ t('FSU Department of Biological Science') }}</a>
    </div>
    <!-- Copyright -->
</footer>
@include('common.php-vars-javascript')
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/admin.js') }}"></script>
@include('common.amchart')
@stack('scripts')