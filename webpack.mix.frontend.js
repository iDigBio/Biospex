let mix = require('laravel-mix');

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

mix.webpackConfig({
    resolve: {
        alias: {
            'jquery-ui': 'jquery-ui/ui/widgets'
        }
    }
})
    .autoload({
        jquery: ['$', 'window.jQuery', "jQuery", "window.$", "jquery", "window.jquery"]
    })
    //.setPublicPath(path.normalize('public'))
    .js('resources/assets/js/app.js', 'js/frontend.js')
    .sass('resources/assets/sass/frontend.scss', 'css/frontend.css')
    .extract([
        'jquery',
        'free-jqgrid/dist/plugins/ui.multiselect',
        'free-jqgrid/js/jquery.jqgrid.min',
        'bootstrap-sass',
        'bootstrap-notify/bootstrap-notify',
        'bootstrap-select',
        'codemirror/lib/codemirror',
        'summernote/dist/summernote',
        'jquery-validation/dist/jquery.validate',
        'jquery-validation/dist/additional-methods',
        'tablesorter/dist/js/jquery.tablesorter',
        'tablesorter/dist/js/jquery.tablesorter.widgets',
        'amcharts3/amcharts/amcharts',
        'amcharts3/amcharts/serial',
        'amcharts3/amcharts/plugins/dataloader/dataloader',
        'amcharts3/amcharts/plugins/responsive/responsive',
        'bootstrap-confirmation2/bootstrap-confirmation',
        'socket.io-client/dist/socket.io',
    ]);


/* Production settings */
/*
if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
*/