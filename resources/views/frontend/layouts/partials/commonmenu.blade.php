<li><a href="{{ route('home.get.vision') }}">{{ trans('pages.vision_menu') }}</a></li>
<li><a href="{{ route('web.faqs.index') }}">{{ trans('pages.faq') }}</a></li>
<li><a href="{{ route('web.resources.index') }}">{{ trans('pages.resources') }}</a></li>
<li><a href="{{ route('home.get.contact') }}">{{ trans('pages.contact') }}</a></li>
<li><a href="{{ route('web.teams.index') }}">{{ trans('pages.team_menu') }}</a></li>
<!-- Navbar Right Menu -->
@can('admin', Auth::getUser())
    <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('pages.admin') }}</a></li>
@endcan
<li class="translate"><div id="google_translate_element"></div><script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
        }
    </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</li>