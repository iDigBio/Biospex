var elixir = require('laravel-elixir');

var assetsDir = './resources/assets/';

var lessPaths = [
    assetsDir + "vendor/bootstrap/less",
    assetsDir + "vendor/font-awesome/less",
    assetsDir + "vendor/bootstrap-select/less"
];

elixir(function (mix) {
    mix.less('app.less', 'public/css/bootstrap.css', {paths: lessPaths});

    mix.styles([
            'css/biospex.css',
            'vendor/jquery-ui/themes/smoothness/jquery-ui.min.css',
            'vendor/jqGrid/css/ui.jqgrid.css',
            'vendor/jqGrid/css/ui.jqgrid-bootstrap.css',
            'vendor/jqGrid/css/ui.jqgrid-bootstrap-ui.css',
            'vendor/ui-multiselect/jquery.multiselect.css',
            'vendor/tablesorter/dist/css/theme.bootstrap.min.css',
        ], 'public/css/biospex.css', assetsDir)
        .scripts([
            'vendor/jquery/dist/jquery.min.js',
            'vendor/jquery-ui/jquery-ui.min.js',
            'vendor/jquery-validation/dist/jquery.validate.min.js',
            'vendor/jquery-validation/dist/additional-methods.min.js',
            'vendor/jqGrid/js/i18n/grid.locale-en.js',
            'vendor/jqGrid/js/jquery.jqGrid.min.js',
            'vendor/ui-multiselect/src/jquery.multiselect.js',
            'vendor/tablesorter/dist/js/jquery.tablesorter.min.js',
            'vendor/tablesorter/dist/js/jquery.tablesorter.widgets.js',
            'js/biospex.js',
            'js/grid.js'
        ], 'public/js/biospex.js', assetsDir);

    mix.scripts([
        'vendor/bootstrap/dist/js/bootstrap.min.js',
        'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
    ], 'public/js/bootstrap.js', assetsDir);

    mix.styles([
            "vendor/AdminLTE/dist/css/AdminLTE.min.css",
            "vendor/AdminLTE/dist/css/skins/skin-blue.min.css",
        ], 'public/css/admin.css', assetsDir)
        .scripts([
            'vendor/AdminLTE/dist/js/app.min.js'
        ], 'public/js/admin.js', assetsDir);

    mix.copy(assetsDir + 'vendor/font-awesome/fonts', 'public/fonts')
        .copy(assetsDir + 'vendor/jquery-ui/themes/smoothness/images', 'public/css/images')
        .copy(assetsDir + 'vendor/AdminLTE/dist/js/pages', 'public/js/pages')
        .copy(assetsDir + 'vendor/AdminLTE/dist/img', 'public/img');
});




