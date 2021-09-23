let mix = require('laravel-mix');
let path = require("path");

mix.js("resources/js/front-app.js", "public/js/front.js")
    .sass("resources/sass/front.scss", "public/css")
    .extract([
        "jquery",
        "bootstrap",
        "bootstrap-notify/bootstrap-notify",
        "socket.io-client/dist/socket.io",
        "confetti-js/dist/index.min"
    ])
    .copy('resources/js/amChartMap.js', 'public/js/amChartMap.js')
    .minify('public/js/amChartMap.js')
    .copy('resources/js/amChartTranscript.js', 'public/js/amChartTranscript.js')
    .minify('public/js/amChartTranscript.js')
    .copy('resources/js/amChartStat.js', 'public/js/amChartStat.js')
    .minify('public/js/amChartStat.js')
    .copy('resources/js/amChartEventRate.js', 'public/js/amChartEventRate.js')
    .minify('public/js/amChartEventRate.js')
    .copy('resources/js/amChartBingo.js', 'public/js/amChartBingo.js')
    .minify('public/js/amChartBingo.js')
    .copy('resources/images', 'public/images').version().disableNotifications();

mix.webpackConfig({
    resolve: {
        alias: {
            'jquery': path.join(__dirname, 'node_modules/jquery/src/jquery'),
            "jquery-ui/sortable": "jquery-ui/ui/widgets/sortable"
        }
    }
}).js("resources/js/admin-app.js", "public/backend/js/admin.js")
    .sass("resources/sass/admin.scss", "public/backend/css")
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
    .minify('public/backend/js/expertReview.js').version().disableNotifications();
