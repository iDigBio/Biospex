var elixir = require('laravel-elixir');

var assetsDir = './resources/assets/';

var lessPaths = [
    assetsDir + "vendor/bootstrap/less",
    assetsDir + "vendor/font-awesome/less",
    assetsDir + "vendor/bootstrap-select/less"
];

elixir(function(mix) {
    mix.less('app.less', assetsDir + '/css', { paths: lessPaths })
        .styles([
            'css/app.css',
            'vendor/jquery-ui/themes/smoothness/jquery-ui.min.css',
            'vendor/jquery.qtip.custom/jquery.qtip.min.css',
            'vendor/jqgrid/css/addons/ui.multiselect.css',
            'vendor/jqgrid/css/ui.jqgrid.css'
        ], 'public/css/app.css', assetsDir)
        .scripts([
            'vendor/jquery/dist/jquery.min.js',
            'vendor/jquery-ui/jquery-ui.min.js',
            'vendor/blockui/jquery.blockUI.js',
            'vendor/jquery.validation/dist/jquery.validate.min.js',
            'vendor/jquery.validation/dist/additional-methods.min.js',
            'vendor/jqgrid/js/addons/ui.multiselect.js',
            'vendor/jquery.qtip.custom/jquery.qtip.min.js',
            'vendor/jqgrid/js/minified/i18n/grid.locale-en.js',
            'vendor/jqgrid/js/minified/jquery.jqGrid.min.js',
            'vendor/bootstrap/dist/js/bootstrap.min.js',
            'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
            'js/app.js',
            'js/grid.js'
        ], 'public/js/app.js', assetsDir)
        .copy(assetsDir + 'vendor/font-awesome/fonts', 'public/fonts')
        .copy(assetsDir + 'vendor/jquery-ui/themes/smoothness/images', 'public/css/images')
        //.version(['css/app.css', 'js/app.js']);

    /*
     mix.less('app.less', assetsDir + '/css', { paths: lessPaths })
     .styles([
     'css/app.css',
     'vendor/jquery.qtip.custom/jquery.qtip.min.css',
     'vendor/jqgrid/css/ui.jqgrid.css',
     'vendor/jqgrid/css/addons/ui.multiselect.css',
     'vendor/admin-lte/dist/css/AdminLTE.min.css',
     'vendor/admin-lte/dist/css/skins/skin-blue.min.css',

     ], 'public/css/app.css', assetsDir)
     .scripts([
     'vendor/jquery/dist/jquery.min.js',
     'vendor/jquery-ui/jquery-ui.min.js',
     'vendor/jquery.validation/dist/jquery.validate.min.js',
     'vendor/jquery.validation/dist/additional-methods.min.js',
     'vendor/jquery.qtip.custom/jquery.qtip.min.js',
     'vendor/jqgrid/js/minified/i18n/grid.locale-en.js',
     'vendor/jqgrid/js/minified/jquery.jqGrid.min.js',
     'vendor/bootstrap/dist/js/bootstrap.min.js',
     'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
     'vendor/admin-lte/dist/js/app.min.js',
     'vendor/admin-lte/dist/js/pages/dashboard.js',
     'vendor/admin-lte/dist/js/pages/dashboard2.js',
     'js/app.js',
     'js/grid.js'
     ], 'public/js/app.js', assetsDir)
     .copy(assetsDir + 'vendor/font-awesome/fonts', 'public/fonts')
     .copy(assetsDir + 'vendor/admin-lte/plugins', 'public/plugins')
     .copy(assetsDir + 'vendor/admin-lte/dist/img', 'public/img')
     .version(['css/app.css', 'js/app.js']);
    */

     });

/*
elixir(function(mix) {
    mix.less('app.less', 'public/css', { paths: lessPaths })
        .scripts([
            'vendor/jquery/dist/jquery.min.js',
            'vendor/bootstrap/dist/js/bootstrap.min.js',
            'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
            'js/app.js',
            'js/grid.js'
        ], 'public/js/app.js', assetsDir)
        .copy(assetsDir + 'vendor/font-awesome/fonts', 'public/fonts')
        .version(['css/app.css', 'js/app.js']);
});
*/

