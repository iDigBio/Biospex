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
mix.webpackConfig({
    resolve: {
        alias: {
            "jquery-ui/sortable": "jquery-ui/ui/widgets/sortable"
        }
    }
}).autoload({
        jquery: ["$", "window.jQuery", "jQuery", "window.$", "jquery", "window.jquery"],
        'bootbox/bootbox.min.js': ['bootbox', 'window.bootbox']
    })
    .js("resources/assets/js/app.js", "js/frontend.js")
    .sass("resources/assets/sass/frontend.scss", "css/frontend.css")
    .extract([
        "jquery",
        "bootstrap-sass/assets/javascripts/bootstrap",
        "bootbox/bootbox.min",

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
        "amcharts3/amcharts/amcharts",
        "amcharts3/amcharts/serial",
        "amcharts3/amcharts/plugins/dataloader/dataloader",
        "amcharts3/amcharts/plugins/responsive/responsive",

        "socket.io-client/dist/socket.io",
    ]);

/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
