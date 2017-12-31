$.jgrid.defaults.width = 780;
$.jgrid.defaults.responsive = true;
$.jgrid.defaults.guiStyle = 'bootstrap';
$.jgrid.cellattr = $.jgrid.cellattr || {};
$.extend($.jgrid.cellattr, {
    addDataAttr: function (rowId, cellVal, rawObject, cm, rdata) {
        return 'data-toggle="modal" data-target="#jqGridModal"';
    }
});


var Grid = {};

$(function () {
    if ($("#jqGridModal").length > 0) {
        'use strict';
        Grid.loadSate = false;
        Grid.id = $(".jgrid").prop('id');
        Grid.obj = $("#" + Grid.id);
        Grid.projectId = Laravel.projectId;
        Grid.expeditionId = Laravel.expeditionId;
        Grid.url = Laravel.url;
        Grid.exportUrl = Laravel.exportUrl;
        Grid.maxSubjects = Laravel.maxSubjects;
        Grid.subjectCountHtmlObj = $('#subjectCountHtml');
        Grid.subjectIdsObj = $('#subjectIds');
        Grid.showCheckbox = Laravel.showCheckbox;
        Grid.explore = Laravel.explore;
        Grid.subjectIdsObj.data('ids', Laravel.subjectIds);

        $.ajax({
            type: "GET",
            url: "/projects/" + Grid.projectId + "/grids/load",
            dataType: "json",
            success: jqBuildGrid()
        });

        $('.gridForm').submit(function () {
            $('#subjectIds').val(Grid.subjectIdsObj.data('ids').toString());

            return true;
        });
    }
});

function jqBuildGrid() {

        return function (result) {
        var cm = result.colModel;
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
            url: Grid.url,
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
            editurl: '/projects/' + Grid.projectId + '/grids/explore',
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

                var i, count, id;
                for (i = 0, count = rowIds.length; i < count; i++) {
                    id = rowIds[i];
                    updateIdsOfSelectedRows(id, isSelected);
                }
                Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
            },
            loadComplete: function () {
                setPreviewLinks();

                if (switchCbColumn() || Grid.loadSate) {
                    Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
                    return;
                }

                setMultipleSelect();

                Grid.subjectCountHtmlObj.html(Grid.subjectIdsObj.data('ids').length);
            }
        }).navGrid("#pager", {
                search: true, // show search button on the toolbar
                add: false,
                edit: false,
                del: true,
                refresh: true,
                closeOnEscape: true,
                closeAfterSearch: true,
                overlay: true,
                cloneToTop: true
            },
            {}, // edit options
            {}, // add options
            {
                onclickSubmit: function (jqXHR) {
                    var $this = $(this), p = $this.jqGrid("getGridParam"), newPage = p.page;

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

            }, // delete options
            {
                width: 600,
                multipleSearch: true,
                recreateFilter: true
            } // search options - define multiple search
        ).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "glyphicon glyphicon-list",
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
            buttonicon: "glyphicon glyphicon-remove",
            title: "Clear saved grid's settings",
            onClickButton: function () {
                localStorage.clear();
                window.location.reload();
            }
        }).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "glyphicon glyphicon-file",
            title: "Export to CSV",
            onClickButton: function (event) {
                event.preventDefault();
                Grid.obj.jqGrid('excelExport',{tag:'excel', url:Grid.exportUrl});
            }
        }).navButtonAdd('#' + Grid.id + '_toppager_left', {
            caption: '',
            buttonicon: "glyphicon glyphicon-list",
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
            buttonicon: "glyphicon glyphicon-remove",
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
        var $grid = $('#' + Grid.id);
        var rowData = $grid.jqGrid('getRowData', id);
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
    var functionsMapping = {
        "imagePreview": function (cellValue, opts, rowObjects) {
            var url = encodeURIComponent(cellValue);
            return '<a href="' + cellValue + '" target="_new">View Image</a>&nbsp;&nbsp;'
                + '<a href="/images/preview?url=' + url + '" class="thumb-view">View Thumb</a>&nbsp;&nbsp;'
                + '<a href="' + cellValue + '" class="url-view">View Url</a>';
        }
    };

    for (var i = 0; i < column.length; i++) {
        var col = column[i];
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
    var $grid = $('#' + Grid.id);
    var ids = $grid.jqGrid('getDataIDs');
    for (var i = 0; i < ids.length; i++) {

        if (!Grid.loadSate && $.inArray(ids[i], Grid.subjectIdsObj.data('ids')) !== -1) {
            $grid.setSelection(ids[i]);
        } else {
            if ($('#' + ids[i]).hasClass("success")) {
                $('#' + ids[i] + ' input[type=checkbox]').prop('checked', true);
                updateIdsOfSelectedRows(value, true);
            }
        }

        if (Grid.explore) {
            var rowData = $grid.jqGrid('getRowData', ids[i]);
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
    var index = $.inArray(id, Grid.subjectIdsObj.data('ids'));
    if (!isSelected && index >= 0) {
        Grid.subjectIdsObj.data('ids').splice($.inArray(id, Grid.subjectIdsObj.data('ids')), 1);
    } else if (index < 0) {
        Grid.subjectIdsObj.data('ids').push(id);
    }

    if (Grid.subjectIdsObj.data('ids').length > Grid.maxSubjects) {
        $('#max').addClass('red');
    }
}

/**
 * Set preview links
 * Must re-declare grid object id for loadState
 */
function setPreviewLinks() {
    $('#' + Grid.id).on("click", 'a.thumb-view', function (event) {
        event.preventDefault();
        $.ajax({
            url: $(event.target).attr('href'),
            beforeSend: function (xhr) {
                $('.loading').show();
            }
        })
            .done(function (data) {
                $('#model-body').html(data);
                $('.loading').hide();
                $('#jqGridModal').modal('show');
            });
    }).on("click", 'a.url-view', function (event) {
        event.preventDefault();
        $('#model-body').html($(event.target).attr('href'));
        $('#jqGridModal').modal('show');
    });
}
