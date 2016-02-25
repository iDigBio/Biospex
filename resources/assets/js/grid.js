$.jgrid.defaults.width = 780;
$.jgrid.defaults.responsive = true;
$.jgrid.defaults.styleUI = 'Bootstrap';
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
        Grid.project = $("#projectId").val();
        Grid.subjectCountObj = $('#subjectCount');
        Grid.subjectIdsObj = $('#subjectIds');
        Grid.subjectIds = Grid.subjectIdsObj.length > 0 ?
            (Grid.subjectIdsObj.val().length == 0 ? [] : Grid.subjectIdsObj.val().split(',')) : '';
        $.ajax({
            type: "GET",
            url: "/projects/" + Grid.project + "/grids/load",
            dataType: "json",
            success: jqBuildGrid()
        });
    }
});

function jqBuildGrid() {

    return function (result) {
        var cm = result.colModel;
        mapFormatter(cm);
        var url = $('#url').val();
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
            url: url,
            mtype: "GET",
            datatype: "json",
            page: 1,
            colNames: result.colNames,
            colModel: cm,
            rowNum: 10,
            gridview: true,
            rowList: [10, 20, 50, 100],
            multiSort: true,
            sortable: true,
            sortname: 'id',
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
            beforeSelectRow: function (id, event) {
                return handleCellSelect(id, event);
            },
            onSelectRow: function (id, isSelected) {
                if (switchCbColumn()) return;
                /*
                 var elem = document.activeElement;
                 if (elem.id) {   // the checkbox has an id, so check for it
                 if (!isSelected){
                 // unselect the select-all if any row is deselected
                 $('#cb_' + Grid.id).attr('checked',false);
                 }
                 } else {
                 // ensure that the row is not checked.
                 $('#' + Grid.id).setSelection(id, false);
                 }
                 */

                updateIdsOfSelectedRows(id, isSelected);
            },
            onSelectAll: function (rowIds, isSelected) {
                if (switchCbColumn()) return;

                var i, count, id;
                for (i = 0, count = rowIds.length; i < count; i++) {
                    id = rowIds[i];
                    updateIdsOfSelectedRows(id, isSelected);
                }
            },
            loadComplete: function () {
                setPreviewLinks();

                if(switchCbColumn() || Grid.loadSate) return;

                setMultipleSelect();
            }

        }).navGrid("#pager", {
                search: true, // show search button on the toolbar
                add: false,
                edit: false,
                del: false,
                refresh: true,
                closeOnEscape: true,
                closeAfterSearch: true,
                overlay: 0
            },
            {}, // edit options
            {}, // add options
            {}, // delete options
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
                    dialog_opts: {
                        modal: true,
                        width: 700,
                        show: 'blind',
                        hide: 'explode'
                    },
                    done: function (perm) {
                        if (perm) {
                            this.jqGrid("remapColumns", perm, true);
                        }
                    }
                });
            }
        }).navButtonAdd('#pager', {
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
    if ($("#showCb").val() == 0) {
        $('#' + Grid.id).jqGrid('hideCol', 'cb');
        return true;
    }

    return false;
}

/**
 * Handle select event for preview cells
 * @param id
 * @param event
 * @returns {boolean}
 */
function handleCellSelect(id, event){
    if (event.target.className == 'ocrPreview') {
        $('#model-body').html($(event.target).text());
        return false;
    }

    if(event.target.className == 'thumbPreview') {
        return false;
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
    for (var x = 0; x < ids.length; x++) {
        if ( ! Grid.loadSate) {
            var index = $.inArray(ids[x], Grid.subjectIds);
            if (index >= 0) {
                var row = $grid.jqGrid('getRowData', ids[x]);
                if (row.expedition_ids == "Yes") {
                    $grid.setSelection(ids[x]);
                }
            }
        } else {
            if ($('#' + ids[x]).hasClass("success")) {
                $('#' + ids[x] + ' input[type=checkbox]').prop('checked', true);
                updateIdsOfSelectedRows(ids[x], true);
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
    var index = $.inArray(id, Grid.subjectIds);
    if (!isSelected && index >= 0) {
        Grid.subjectIds = $.grep(Grid.subjectIds, function (val) {
            return val != id;
        });
    } else if (index < 0) {
        Grid.subjectIds.push(id);
    }
    Grid.subjectIdsObj.val(Grid.subjectIds);
    Grid.subjectCountObj.html(Grid.subjectIds.length);
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
