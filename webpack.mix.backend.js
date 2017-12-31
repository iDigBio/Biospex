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
    .setPublicPath(path.normalize('public/backend'))
    .js('resources/assets/js/backend.js', 'js/backend.js')
    .sass('resources/assets/sass/backend.scss', 'css/backend.css')
    .extract([
        'jquery',
        'bootstrap-sass',
        'bootstrap-notify/bootstrap-notify',
        'bootstrap-confirmation2/bootstrap-confirmation',
        'x-editable/dist/bootstrap3-editable/js/bootstrap-editable',
        'x-editable/dist/inputs-ext/wysihtml5/wysihtml5',
        //'jquery-ujs/src/rails',
        //'toastr',
        'icheck',
        'admin-lte/dist/js/adminlte',
    ]);


/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
