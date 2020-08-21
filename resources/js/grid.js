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
        Grid.projectId = Laravel.projectId;
        Grid.expeditionId = Laravel.expeditionId;
        Grid.loadUrl = Laravel.loadUrl;
        Grid.gridUrl = Laravel.gridUrl;
        Grid.exportUrl = Laravel.exportUrl;
        Grid.editUrl = Laravel.editUrl;
        Grid.maxSubjects = Laravel.maxSubjects;
        Grid.subjectCountHtmlObj = $('#subject-count-html');
        Grid.subjectIdsObj = $('#subject-ids');
        Grid.showCheckbox = Laravel.showCheckbox;
        Grid.explore = Laravel.explore;
        Grid.subjectIdsObj.data('ids', Laravel.subjectIds);

        $.ajax({
            type: "GET",
            url: Grid.loadUrl,
            dataType: "json",
            success: jqBuildGrid()
        });

        $('#gridForm').submit(function () {
            $('#subject-ids').val(Grid.subjectIdsObj.data('ids').toString());

            return true;
        });
    }
});

function jqBuildGrid() {

        return function (result) {
        let cm = result.colModel;
        mapFormatter(cm);
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
            rowList: [20, 50, 100, 500],
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
            editurl: Grid.editUrl,
            guiStyle: "bootstrap4",
            iconSet: "fontAwesome",
            beforeSelectRow: function (id, event) {
                return handleCellSelect(id, event);
            },
            onSelectRow: function (id, isSelected) {
                if (switchCbColumn()) return;

                updateIdsOfSelectedRows(id, isSelected);
                Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
            },
            onSelectAll: function (rowIds, isSelected) {
                if (switchCbColumn()) return;

                let i, count, id;
                for (i = 0, count = rowIds.length; i < count; i++) {
                    id = rowIds[i];
                    updateIdsOfSelectedRows(id, isSelected);
                }
                Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
            },
            loadComplete: function () {
                if (switchCbColumn() || Grid.loadSate) {
                    Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
                    return;
                }

                setMultipleSelect();

                Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
            }
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
            {
                onclickSubmit: function (jqXHR) {
                    let $this = $(this), p = $this.jqGrid("getGridParam"), newPage = p.page;

                    if (p.lastpage > 1) {// on the multipage grid reload the grid
                        if (p.reccount === 1 && newPage === p.lastpage) {
                            // if after deliting there are no rows on the current page
                            // which is the last page of the grid
                            newPage--; // go to the previous page
                        }
                        // reload grid to make the row from the next page visible.
                        setTimeout(function () {
                            $this.trigger("reloadGrid", [{page: newPage}]);
                        }, 50);
                    }

                    //Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
                    //Grid.subjectCountHtmlObj.html(Grid.obj.getGridParam("records"));

                    return true;
                },
                delData: {
                    projectId: Grid.projectId,
                    expeditionId: Grid.expeditionId,
                    _token: $('meta[name=csrf-token]').attr('content')
                }

            }, // prmDel
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
        }).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "fas fa-file-export",
            title: "Export to CSV",
            onClickButton: function (event) {
                $.get(Grid.exportUrl, function( data ) {
                    alert( "The export will be emailed when compiled.");
                });
                return false;
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
        }).navButtonAdd('#' + Grid.id + '_toppager_left', {
            caption: '',
            buttonicon: "fas fa-file-export",
            title: "Export to CSV",
            onClickButton: function (event) {
                $.get(Grid.exportUrl, function( data ) {
                    alert( "The export will be emailed when compiled.");
                });
                return false;
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
            setMultipleSelect();
        });
    };
}

/**
 * Switch checkbox column
 * Must re-declare grid id object for loadState
 * @returns {boolean}
 */
function switchCbColumn() {
    if (!Grid.showCheckbox) {
        $('#' + Grid.id).jqGrid('hideCol', 'cb');
        return true;
    }

    if (Grid.explore) {
        $("#cb_jqGridExplore").attr("disabled", "disabled");
    }

    return false;
}

/**
 * Handle select event for preview cells
 * @param id
 * @param event
 * @returns {boolean}
 */
function handleCellSelect(id, event) {
    if (event.target.className === 'ocrPreview') {
        $('#model-body').html($(event.target).text());
        return false;
    }

    if (event.target.className === 'thumbPreview') {
        return false;
    }

    if (Grid.explore) {
        let $grid = $('#' + Grid.id);
        let rowData = $grid.jqGrid('getRowData', id);
        if (rowData['expedition_ids'] === 'Yes') {
            return false;
        }
    }

    return true;
}

/**
 * Map formatter
 * @param column
 */
function mapFormatter(column) {
    let functionsMapping = {
        "imagePreview": function (cellValue, opts, rowObjects) {
            let url = encodeURIComponent(cellValue);
            return '<a href="' + cellValue + '" target="_new">View Image</a>&nbsp;&nbsp;'
                + '<a href="#" class="thumb-view" data-remote="/admin/images/preview?url=' + url + '" data-toggle="modal" data-target="#jqgrid-modal" data-hover="tooltip" title="Preview Thumbnail">View Thumb</a>&nbsp;&nbsp;'
                + '<a href="#" class="url-view" data-remote="' + cellValue + '" data-toggle="modal" data-target="#jqgrid-modal" data-hover="tooltip" title="Preview URL">View URL</a>'
        }
    };

    for (let i = 0; i < column.length; i++) {
        let col = column[i];
        if (col.hasOwnProperty("formatter") &&
            functionsMapping.hasOwnProperty(col.formatter)) {
            col.formatter = functionsMapping[col.formatter];
        }
    }
}

/**
 * Set selected rows
 * Must re-declare grid object for loadState and handle it
 * differently due to ids not being
 */
function setMultipleSelect() {
    let $grid = $('#' + Grid.id);
    let ids = $grid.jqGrid('getDataIDs');
    for (let i = 0; i < ids.length; i++) {

        if (!Grid.loadSate && $.inArray(ids[i], Grid.subjectIdsObj.data('ids')) !== -1) {
            $grid.setSelection(ids[i]);
        } else {
            if ($('#' + ids[i]).hasClass("success")) {
                $('#' + ids[i] + ' input[type=checkbox]').prop('checked', true);
                updateIdsOfSelectedRows(value, true);
            }
        }

        if (Grid.explore) {
            let rowData = $grid.jqGrid('getRowData', ids[i]);
            if (rowData['expedition_ids'] === 'Yes') {
                $('#' + ids[i] + ' input[type=checkbox]').prop('disabled', true);
            }
        }
    }

    Grid.loadSate = false;
}

/**
 * Update ids for selected rows
 * @param id
 * @param isSelected
 */
function updateIdsOfSelectedRows(id, isSelected) {
    let index = $.inArray(id, Grid.subjectIdsObj.data('ids'));
    if (!isSelected && index >= 0) {
        Grid.subjectIdsObj.data('ids').splice($.inArray(id, Grid.subjectIdsObj.data('ids')), 1);
    } else if (index < 0) {
        Grid.subjectIdsObj.data('ids').push(id);
    }

    if (Grid.subjectIdsObj.data('ids').length > Grid.maxSubjects) {
        $('#max').addClass('red');
    }
}
