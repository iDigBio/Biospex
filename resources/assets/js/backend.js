/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    require('jquery/dist/jquery');
//    require('jquery-ui-dist/jquery-ui');
    require('bootstrap-sass/assets/javascripts/bootstrap');
//    require('bootstrap-wysiwyg/js/bootstrap-wysiwyg.min');
//    require('x-editable/dist/bootstrap3-editable/js/bootstrap-editable');
//    require('x-editable/dist/inputs-ext/wysihtml5/wysihtml5');
//    require('jquery-ujs/src/rails');
//    require('icheck/icheck');
    require('admin-lte/dist/js/adminlte');
//    require('bootstrap-confirmation2/bootstrap-confirmation');
//    require('./delete-form');
    require('./adminlte');
    require('./flash');

} catch (e) {}
