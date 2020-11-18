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
        "confetti-js/dist/index.min"
    ])
    .copy('resources/js/amChartMap.js', 'public/js/amChartMap.js')
    .minify('public/js/amChartMap.js')
    .copy('resources/js/amChartTranscript.js', 'public/js/amChartTranscript.js')
    .minify('public/js/amChartTranscript.js')
    .copy('resources/js/amChartStat.js', 'public/js/amChartStat.js')
    .minify('public/js/amChartStat.js')
    .copy('resources/js/amChartEventRate.js', 'public/js/amChartEventRate.js')
    .minify('public/js/amChartEventRate.js')
    .copy('resources/js/amChartBingo.js', 'public/js/amChartBingo.js')
    .minify('public/js/amChartBingo.js')
    .copy('resources/images', 'public/images');
/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
