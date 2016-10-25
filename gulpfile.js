var elixir = require('laravel-elixir');
require('laravel-elixir-imagemin');
require('laravel-elixir-replace');

var replacements = [
    ['./lib/css/wysiwyg-color.css', ''],
    ['blue.png', '../img/blue.png'],
    ['blue@2x.png', '../img/blue@2x.png'],
    ['images/', '../img/'],
];

var assets = './resources/assets';

var lessPaths = [
    assets + "/vendor/bootstrap/less"
];

elixir(function (mix) {
    // copy needed images and fonts to assets/img and assets/fonts directory
    mix.copy(assets + '/vendor/font-awesome/fonts/', assets + '/fonts')
        .copy(assets + '/vendor/bootstrap/fonts', assets + '/fonts')
        .copy(assets + '/vendor/jquery-ui/themes/smoothness/images', assets + '/img')
        .copy(assets + '/vendor/AdminLTE/dist/img', assets + '/img')
        .copy(assets + '/vendor/iCheck/skins/minimal/blue.png', assets + '/img/blue.png')
        .copy(assets + '/vendor/iCheck/skins/minimal/blue@2x.png', assets + '/img/blue@2x.png')
        .copy(assets + '/vendor/x-editable/dist/bootstrap3-editable/img', assets + '/img')
        .copy(assets + '/vendor/amcharts3/amcharts/images', assets + '/img');

    // minify images
    elixir.config.images = {
        folder: 'img',
        outputFolder: 'img'
    };
    mix.imagemin().copy(assets + '/img', 'public/img');

    //minify fonts
    elixir.config.images = {
        folder: 'fonts',
        outputFolder: 'fonts'
    };
    mix.imagemin().copy(assets + '/fonts', 'public/fonts');

    // mix bootstrap using less and app specific less
    mix.less('app.less', 'resources/assets/css/bootstrap.css', {paths: lessPaths});

    // Create frontend css and javascript
    mix.styles([
            'css/bootstrap.css',
            '/vendor/font-awesome/css/font-awesome.min.css',
            'vendor/jquery-ui/themes/smoothness/jquery-ui.min.css',
            'vendor/jquery-ui-multiselect-widget/jquery.multiselect.css',
            'vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
            'vendor/tablesorter/dist/css/theme.bootstrap.min.css',
            'vendor/jqGrid/css/ui.jqgrid-bootstrap.css',
            'css/biospex.css'
        ], 'public/css/frontend.css', assets)
        .scripts([
            '/vendor/jquery/dist/jquery.min.js',
            '/vendor/jquery-ui/jquery-ui.min.js',
            '/vendor/bootstrap/dist/js/bootstrap.min.js',
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
        ], 'public/js/frontend.js', assets);

    // Create backend css and javascript
    mix.styles([
            'css/bootstrap.css',
            '/vendor/font-awesome/css/font-awesome.min.css',
            '/vendor/bootstrap3-wysihtml5-bower/dist/bootstrap3-wysihtml5.min.css',
            '/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',
            '/vendor/toastr/toastr.min.css',
            '/vendor/iCheck/skins/minimal/blue.css',
            '/vendor/AdminLTE/dist/css/AdminLTE.min.css',
            '/vendor/AdminLTE/dist/css/skins/skin-blue.min.css',
            '/css/adminlte.css'
        ], 'public/css/backend.css', assets)
        .scripts([
            '/vendor/jquery/dist/jquery.min.js',
            '/vendor/jquery-ui/jquery-ui.min.js',
            '/vendor/bootstrap/dist/js/bootstrap.min.js',
            '/vendor/bootstrap3-wysihtml5-bower/dist/bootstrap3-wysihtml5.all.min.js',
            '/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',
            '/vendor/x-editable/dist/inputs-ext/wysihtml5/wysihtml5.js',
            '/vendor/jquery-ujs/src/rails.js',
            '/vendor/toastr/toastr.min.js',
            '/vendor/iCheck/icheck.min.js',
            '/vendor/AdminLTE/dist/js/app.min.js',
            '/vendor/bs-confirmation/bootstrap-confirmation.min.js',
            '/js/adminlte.js',
            '/js/delete-form.js'
        ], 'public/js/backend.js', assets);

    // replace urls for images and fonts
    mix.replace('public/js/backend.js', replacements)
        .replace('public/css/backend.css', replacements)
        .replace('public/js/frontend.js', replacements)
        .replace('public/css/frontend.css', replacements);
});