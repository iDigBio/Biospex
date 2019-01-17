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
    //jquery: ["$", "window.jQuery", "jQuery", "window.$", "jquery", "window.jquery"],
    //'resources/js/bootbox.js': ['bootbox', 'window.bootbox']
})
    .js("resources/js/app.js", "js/front.js")
    .sass("resources/sass/front.scss", "css/front.css")
//mix.copy("node_modules/amcharts3/amcharts/images", "public/images/vendor/amchart");
    .extract([
        "jquery",
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

        "socket.io-client/dist/socket.io",
    ]);
/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}
