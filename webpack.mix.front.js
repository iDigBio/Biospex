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
mix.autoload({
    //jquery: ["$", "window.jQuery", "jQuery", "window.$", "jquery", "window.jquery"],
    //'resources/js/bootbox.js': ['bootbox', 'window.bootbox']
})
    .js("resources/js/front-app.js", "js/front.js")
    .sass("resources/sass/front.scss", "css/front.css")
    //mix.copy("node_modules/amcharts3/amcharts/images", "public/images/vendor/amchart");
    .extract([
        "jquery",
        "bootstrap",
        "bootstrap-notify/bootstrap-notify",
        "socket.io-client/dist/socket.io",
        '@amcharts/amcharts4/core',
        '@amcharts/amcharts4/charts'
    ]);
/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
