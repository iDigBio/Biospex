/**
 * We"ll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    require("select2/dist/js/select2.full");
    require("jquery-ui/ui/disable-selection");
    require("jquery-ui/ui/widgets/selectable");
    require("jquery-ui/ui/widgets/sortable");
    require("jquery-ui/ui/widgets/draggable");
    require("jquery-ui/ui/widgets/droppable");

    require("bootstrap-sass/assets/javascripts/bootstrap");
    require("bootstrap-notify/bootstrap-notify");
    require("bootstrap-confirmation2/bootstrap-confirmation");
    require("x-editable/dist/bootstrap3-editable/js/bootstrap-editable");

    require("codemirror/lib/codemirror");
    require("summernote/dist/summernote");

    require("jquery-ujs/src/rails");
    require("icheck/icheck");
    require("admin-lte/dist/js/adminlte");
    require("./delete-form");
    require("./adminlte");
    require("./flash");

} catch (e) {
}
