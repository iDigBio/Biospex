let mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.js("resources/js/front-app.js", "js/front.js")
    .sass("resources/sass/front.scss", "css/front.css")
    .extract([
        "jquery",
        "bootstrap",
        "bootstrap-notify/bootstrap-notify",
        "socket.io-client/dist/socket.io",
    ])
    .copy('resources/js/amChartMap.js', 'public/js/amChartMap.js')
    .babel('public/js/amChartMap.js', 'public/js/amChartMap.js')
    .minify('public/js/amChartMap.js')
    .copy('resources/js/amChartTranscript.js', 'public/js/amChartTranscript.js')
    .babel('public/js/amChartTranscript.js', 'public/js/amChartTranscript.js')
    .minify('public/js/amChartTranscript.js')
    .copy('resources/js/amChartStat.js', 'public/js/amChartStat.js')
    .babel('public/js/amChartStat.js', 'public/js/amChartStat.js')
    .minify('public/js/amChartStat.js')
    .copy('resources/images/contributors', 'public/storage/images/contributors')
    .copy('resources/images/habitat-banners', 'public/storage/images/habitat-banners')
    .copy('resources/images/page', 'public/storage/images/page')
    .copy('resources/images/page-banners', 'public/storage/images/page-banners')
    .copy('resources/images/placeholders', 'public/storage/images/placeholders')
    .copy('resources/images/slider', 'public/storage/images/slider');
/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
