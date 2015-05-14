var elixir = require('laravel-elixir');

var assetsDir = './resources/assets/';

var lessPaths = [
    assetsDir + "vendor/bootstrap/less",
    assetsDir + "vendor/font-awesome/less",
    assetsDir + "vendor/bootstrap-select/less"
];

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
