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
            'jquery': path.join(__dirname, 'node_modules/jquery/src/jquery'),
            "jquery-ui/sortable": "jquery-ui/ui/widgets/sortable"
        }
    }
}).setResourceRoot('../')
    .setPublicPath(path.normalize("public/backend"))
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
    ])
    .copy('resources/js/jquery.form.min.js', 'public/backend/js/jquery.form.min.js')
    .copy('resources/js/expertReview.js', 'public/backend/js/expertReview.js')
    .minify('public/backend/js/expertReview.js');

mix.version();
mix.disableNotifications();