const mix = require('laravel-mix');

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
            "jquery-ui/sortable": "jquery-ui/ui/widgets/sortable"
        }
    }
}).autoload({
    jquery: ["$", "window.jQuery", "jQuery", "window.$", "jquery", "window.jquery"]
}).js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');
/*
.extract([

    "jquery",
    "bootstrap-sass/assets/javascripts/bootstrap"

    "bootbox",

    "jquery-ui/ui/widgets/dialog",
    "jquery-ui/ui/widgets/draggable",
    "jquery-ui/ui/widgets/droppable",
    "jquery-ui/ui/widgets/resizable",
    "jquery-validation/dist/jquery.validate",
    "jquery-validation/dist/additional-methods",
    "jquery-datetimepicker/build/jquery.datetimepicker.full",
    "free-jqgrid/dist/plugins/ui.multiselect",
    "free-jqgrid/js/jquery.jqgrid.min",

    "bootstrap-notify/bootstrap-notify",

    "codemirror/lib/codemirror",
    "summernote/dist/summernote",

    "tablesorter/dist/js/jquery.tablesorter",
    "tablesorter/dist/js/jquery.tablesorter.widgets",
    "@amcharts/amcharts4/core",
    "@amcharts/amcharts4/charts"
]);
*/

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
