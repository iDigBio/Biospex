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
    jquery: ["$", "window.jQuery", "jQuery", "window.$", "jquery", "window.jquery"]
})
    .js("resources/js/app.js", "js/front.js")
    .sass("resources/sass/front.scss", "css/front.css")
    .extract([
        //"jquery",
        //"bootstrap",
        //"@fortawesome/fontawesome-free",
        //"hamburgers",
        //"holderjs",

        //"jquery-ui/ui/widgets/dialog",
        //"jquery-ui/ui/widgets/draggable",
        //"jquery-ui/ui/widgets/droppable",
        //"jquery-ui/ui/widgets/resizable",
        "jquery-validation/dist/jquery.validate",
        "jquery-validation/dist/additional-methods",
        "jquery-datetimepicker/build/jquery.datetimepicker.full",
        //"free-jqgrid/dist/plugins/ui.multiselect",
        //"free-jqgrid/js/jquery.jqgrid.min",

        //"aos/dist/aos",

        "bootstrap-notify/bootstrap-notify",

        //"codemirror/lib/codemirror",
        //"summernote/dist/summernote",

        "tablesorter/dist/js/jquery.tablesorter",
        "tablesorter/dist/js/jquery.tablesorter.widgets",
        //"amcharts3/amcharts/amcharts",
        //"amcharts3/amcharts/serial",
        //"amcharts3/amcharts/plugins/dataloader/dataloader",
        //"amcharts3/amcharts/plugins/responsive/responsive",

        "socket.io-client/dist/socket.io"
    ]);
//mix.copy("node_modules/amcharts3/amcharts/images", "public/images/vendor/amchart");

/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
