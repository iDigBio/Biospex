var elixir = require('laravel-elixir');

var assetsDir = './resources/assets/';

var lessPaths = [
    assetsDir + "vendor/bootstrap/less"
];

elixir(function (mix) {
    mix.less('app.less', 'resources/assets/css/bootstrap.css', {paths: lessPaths})
        .styles([
            'css/bootstrap.css',
            'vendor/jquery-ui-multiselect-widget/jquery.multiselect.css',
            'vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
            'vendor/tablesorter/dist/css/theme.bootstrap.min.css',
            'vendor/jqGrid/css/ui.jqgrid-bootstrap.css',
            'css/biospex.css'
        ], 'public/css/biospex.css', assetsDir)
        .scripts([
            'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
            'vendor/jquery-validation/dist/jquery.validate.min.js',
            'vendor/jquery-validation/dist/additional-methods.min.js',
            'vendor/jquery-ui-multiselect-widget/src/jquery.multiselect.js',
            'vendor/tablesorter/dist/js/jquery.tablesorter.min.js',
            'vendor/tablesorter/dist/js/jquery.tablesorter.widgets.js',
            'vendor/amcharts3/amcharts/amcharts.js',
            'vendor/amcharts3/amcharts/serial.js',
            'vendor/amcharts3/amcharts/plugins/dataloader/dataloader.min.js',
            'vendor/amcharts3/amcharts/plugins/responsive/responsive.min.js',
            'vendor/jqGrid/js/i18n/grid.locale-en.js',
            'vendor/jqGrid/js/jquery.jqGrid.min.js',
            'vendor/bs-confirmation/bootstrap-confirmation.min.js',
            'js/amchart.js',
            'js/delete-form.js',
            'js/biospex.js',
            'js/grid.js'
        ], 'public/js/biospex.js', assetsDir)

        .styles([
            'vendor/AdminLTE/dist/css/AdminLTE.min.css',
            'vendor/AdminLTE/dist/css/skins/skin-blue.min.css',
            'vendor/AdminLTE/plugins/iCheck/square/blue.css',
            'css/adminlte.css'
        ], 'public/adminlte/css/main.css', assetsDir)
        .scripts([
            'vendor/AdminLTE/dist/js/app.min.js',
            'vendor/bs-confirmation/bootstrap-confirmation.min.js',
            'js/adminlte.js',
            'js/delete-form.js'
        ], 'public/adminlte/js/main.js', assetsDir)

        .copy(assetsDir + 'vendor/bootstrap/fonts', 'public/fonts')
        .copy(assetsDir + 'vendor/AdminLTE/dist/img', 'public/adminlte/img')
        .copy(assetsDir + 'vendor/amcharts3/amcharts/images', 'public/amcharts/images');
});




