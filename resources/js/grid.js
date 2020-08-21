$.jgrid.defaults.width = 780;
$.jgrid.defaults.responsive = true;
$.jgrid.cellattr = $.jgrid.cellattr || {};

let Grid = {};

$(function () {
    if ($("#jqgrid-modal").length > 0) {
        'use strict';
        Grid.loadSate = false;
        Grid.id = $(".jgrid").prop('id');
        Grid.obj = $("#" + Grid.id);
        Grid.loadUrl = Laravel.loadUrl;
        Grid.gridUrl = Laravel.gridUrl;

        $.ajax({
            type: "GET",
            url: Grid.loadUrl,
            dataType: "json",
            success: jqBuildGrid()
        });
    }
});

function jqBuildGrid() {

    return function (result) {
        let cm = result.colModel;

        Grid.obj.jqGrid({
            jsonReader: {
                repeatitems: false,
                root: "rows",
                page: "page",
                total: "total",
                records: "records",
                cell: "",
                id: "_id"
            },
            url: Grid.gridUrl,
            mtype: "GET",
            datatype: "json",
            page: 1,
            colNames: result.colNames,
            colModel: cm,
            rowNum: 20,
            gridview: true,
            rowList: [20, 50, 100],
            multiSort: true,
            sortable: true,
            sortorder: 'asc',
            mulitpleSearch: true,
            multiselect: true,
            multiboxonly: true,
            viewrecords: true,
            shrinkToFit: true,
            autowidth: true,
            storeNavOptions: true,
            height: '100%',
            pager: "#pager",
            toppager: true,
            guiStyle: "bootstrap4",
            iconSet: "fontAwesome"
            //navGrid(element, {parameters}, prmEdit, prmAdd, prmDel, prmSearch, prmView);
        }).navGrid("#pager", {
                search: true,
                add: false,
                edit: false,
                del: true,
                refresh: true,
                closeOnEscape: true,
                closeAfterSearch: true,
                overlay: true,
                cloneToTop: true
            }, // {parameters}
            {}, // prmEdit
            {}, // prmAdd
            {}, // prmDel
            {
                top: 'auto',
                width: 700,
                multipleSearch: true,
                recreateFilter: true,
            }, // prmSearch
            {} // prmView
        ).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "fas fa-columns",
            title: "Choose columns",
            onClickButton: function () {
                Grid.obj.jqGrid('columnChooser', {
                    classname: "columnChooser",
                    modal: true,
                    width: 600,
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                        }
                    }
                });
                $('.ui-multiselect ul.selected').height('500px');
                $('.ui-multiselect ul.available').height('500px');
            }
        }).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "fas fa-eraser",
            title: "Clear saved grid's settings",
            onClickButton: function () {
                localStorage.clear();
                window.location.reload();
            }
        }).navButtonAdd('#' + Grid.id + '_toppager_left', {
            caption: '',
            buttonicon: "fas fa-columns",
            title: "Choose columns",
            onClickButton: function () {
                Grid.obj.jqGrid('columnChooser', {
                    classname: "columnChooser",
                    modal: true,
                    width: 600,
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                        }
                    }
                });
                $('.ui-multiselect ul.selected').height('500px');
                $('.ui-multiselect ul.available').height('500px');
            }
        }).navButtonAdd('#' + Grid.id + '_toppager_left', {
            caption: '',
            buttonicon: "fas fa-eraser",
            title: "Clear saved grid's settings",
            onClickButton: function () {
                localStorage.clear();
                window.location.reload();
            }
        });

        $('#savestate').click(function (event) {
            event.preventDefault();
            $.jgrid.saveState(Grid.id);
        });

        $('#loadstate').click(function (event) {
            event.preventDefault();
            Grid.loadSate = true;
            $.jgrid.loadState(Grid.id);

        });
    };
}

