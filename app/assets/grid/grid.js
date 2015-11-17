$.jgrid.defaults.width = 780;
$.jgrid.defaults.responsive = true;
$.jgrid.defaults.styleUI = 'Bootstrap';
$.jgrid.cellattr = $.jgrid.cellattr || {};
$.extend($.jgrid.cellattr, {
    addDataAttr: function (rowId, cellVal, rawObject, cm, rdata) {
        return 'data-toggle="modal" data-target="#jqGridModal"';
    }
});

$(function () {
    'use strict';
    var gridId = $(".jgrid").prop('id');
    var $grid = $("#" + gridId);
    var project = $("#projectId").val();
    $.ajax({
        type: "GET",
        url: "/projects/" + project + "/grids/load",
        dataType: "json",
        success: jqBuildGrid($grid, gridId)
    });
});

function jqBuildGrid($grid, gridId) {
    if ($('#subjectIds').length > 0) {
        var subjectIds = $('#subjectIds').val().length == 0 ? [] : $('#subjectIds').val().split(',');
    }

    return function (result) {
        var cm = result.colModel;
        mapFormatter(cm);
        var url = $('#url').val();
        $grid.jqGrid({
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

                if (event.target.className == 'ocrPreview') {
                    $('#model-body').html($(event.target).text());
                    return false;
                }

                if (event.target.className == 'thumbPreview') {
                    return false;
                }
            },
            onSelectRow: function (id, isSelected) {
                if ($("#showCb").val() == 0) {
                    return;
                }

                updateIdsOfSelectedRows(id, isSelected);
            },
            onSelectAll: function (aRowids, isSelected) {
                if ($("#showCb").val() == 0) {
                    return;
                }

                var i, count, id;
                for (i = 0, count = aRowids.length; i < count; i++) {
                    id = aRowids[i];
                    updateIdsOfSelectedRows(id, isSelected);
                }
            },
            loadComplete: function () {
                var $this = $(this);

                setPreviewLinks(this.id);

                if ($("#showCb").val() == 0) {
                    $this.jqGrid('hideCol', 'cb');
                    return;
                }

                setMultipleSelect($this);
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
                //multipleGroup: true,
                recreateFilter: true
            } // search options - define multiple search
        ).navButtonAdd('#pager', {
            caption: '',
            buttonicon: "glyphicon glyphicon-list",
            title: "Choose columns",
            onClickButton: function () {
                $(this).jqGrid('columnChooser', {
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
            $.jgrid.saveState(gridId);
        });

        $('#loadstate').click(function (event) {
            event.preventDefault();
            $.jgrid.loadState(gridId);
        });
    };
}

function mapFormatter(column) {
    var functionsMapping = {
        "imagePreview": function (cellValue, opts, rowObjects) {
            var url = encodeURIComponent(cellValue);
            return '<a href="' + cellValue + '" target="_new">View Image</a>'
                + '<a href="/images/preview?url=' + url + '" class="thumb-view">View Thumb</a>'
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

function setMultipleSelect($this) {
    var data = subjectIds;
    for (var x = 0; x < data.length; x++) {
        var row = $this.jqGrid ('getRowData', data[x]);
        if (row.expedition_ids == "Yes") {
            $this.setSelection(data[x]);
        }
    }
}

function updateIdsOfSelectedRows(id, isSelected) {
    var index = $.inArray(id, subjectIds);
    if (!isSelected && index >= 0) {
        subjectIds = $.grep(subjectIds, function (val) {
            return val != id;
        });
    } else if (index < 0) {
        subjectIds.push(id);
    }
    $('#subjectIds').val(subjectIds);
    $('#subjectCount').html(subjectIds.length);
}

function setPreviewLinks(id) {
    $('#'+id).on("click", 'a.thumb-view', function (event) {
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
