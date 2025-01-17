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
            'jQuery': path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
            "jquery-ui/sortable": "jquery-ui/ui/widgets/sortable"
        }
    }
});

mix.js("resources/js/front-app.js", "js/front.js")
    .js("resources/js/admin-app.js", "js/admin.js")
    .sass("resources/sass/front.scss", "css/front.css")
    .sass("resources/sass/admin.scss", "css/admin.css")
    .extract([
        "jquery",
        "bootstrap",
        "bootstrap-select/dist/js/bootstrap-select.min",
        "bootstrap-notify/bootstrap-notify",
        "confetti-js/dist/index.min",
        "jquery-ui/ui/widgets/dialog",
        "jquery-ui/ui/widgets/draggable",
        "jquery-ui/ui/widgets/droppable",
        "jquery-ui/ui/widgets/resizable",
        "jquery-datetimepicker/build/jquery.datetimepicker.full",
        "free-jqgrid/dist/plugins/ui.multiselect",
        "free-jqgrid/js/jquery.jqgrid.min",
        "codemirror/lib/codemirror",
        "summernote/dist/summernote",
    ])
    .copy('resources/js/amChartMap.js', 'public/js/amChartMap.js')
    .minify('public/js/amChartMap.js')
    .copy('resources/js/amChartTranscript.js', 'public/js/amChartTranscript.js')
    .minify('public/js/amChartTranscript.js')
    .copy('resources/js/amChartStat.js', 'public/js/amChartStat.js')
    .minify('public/js/amChartStat.js')
    .copy('resources/js/amChartEventRate.js', 'public/js/amChartEventRate.js')
    .minify('public/js/amChartEventRate.js')
    .copy('resources/js/amChartWeDigBioRate.js', 'public/js/amChartWeDigBioRate.js')
    .minify('public/js/amChartWeDigBioRate.js')
    .copy('resources/js/amChartBingo.js', 'public/js/amChartBingo.js')
    .minify('public/js/amChartBingo.js')
    .copy('resources/images', 'public/images')
    .copy('resources/js/jquery.form.min.js', 'public/js/jquery.form.min.js')
    .copy('resources/js/expertReview.js', 'public/js/expertReview.js')
    .minify('public/js/expertReview.js')
    .version()
    .disableNotifications();
