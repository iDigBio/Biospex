let mix = require("laravel-mix");
let path = require("path");

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
}).setResourceRoot('../')
    .setPublicPath(path.normalize("public/admin"))
    .js("resources/js/admin-app.js", "js/admin.js")
    .sass("resources/sass/admin.scss", "css/admin.css")
    .extract([
        "jquery",
        "bootstrap",
        "bootstrap-notify/bootstrap-notify",
        "jquery-ui/ui/widgets/dialog",
        "jquery-ui/ui/widgets/draggable",
        "jquery-ui/ui/widgets/droppable",
        "jquery-ui/ui/widgets/resizable",
        "jquery-datetimepicker/build/jquery.datetimepicker.full",
        "free-jqgrid/dist/plugins/ui.multiselect",
        "free-jqgrid/js/jquery.jqgrid.min",

        "codemirror/lib/codemirror",
        "summernote/dist/summernote",
        "socket.io-client/dist/socket.io",
    ]);


/* Production settings */

if (mix.inProduction()) {
    mix.version();
    mix.disableNotifications();
}