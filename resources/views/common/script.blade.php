<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/frontend.js') }}"></script>
<script>
    document.querySelector('html').classList.remove('no-js');
    if (!window.Cypress) {
        const scrollCounter = document.querySelector('.js-scroll-counter');
        AOS.init({
            mirror: true
        });
        document.addEventListener('aos:in', function(e) {
            console.log('in!', e.detail);
        });
        window.addEventListener('scroll', function() {
            scrollCounter.innerHTML = window.pageYOffset;
        });
    }
</script>
<script>var scroll = new SmoothScroll('a[href*="#"]');</script>