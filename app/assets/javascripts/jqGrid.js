/**
 * Created by Robert on 11/20/2014.
 */
$(function() {
    if ($("#jqgrid").length) {
        var projectId = $("#projectId").val();
        var expeditionId = $("#expeditionId").val();
        $.ajax(
            {
                type: "GET",
                url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grids/load",
                dataType: "json",
                success: function(result)
                {
                    var colN = result.colNames;
                    var colM = result.colModel;

                    $("#jqgrid").jqGrid({
                        jsonReader : {
                            repeatitems: false,
                            root:"rows",
                            page: "page",
                            total: "total",
                            records: "records",
                            id: "_id"
                        },
                        url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grids",
                        datatype: 'json',
                        mtype: 'GET',
                        colNames:colN,
                        colModel :colM,
                        pager: jQuery('#jqpager'),
                        rowNum: 10,
                        rowList: [10, 20, 50],
                        viewrecords: true,
                        gridview: true,
                        shrinkToFit: false,
                        width: null,
                        emptyrecords: "No records to view",
                        rownumbers: true,
                        sortname: "id",
                        viewrecords: true,
                        sortorder: "desc",
                        autoencode: true,
                        loadonce: false,
                        height: "auto",
                        caption: "Subjects",
                        gridComplete: function () {
                            $(this).parent().append('<span id="widthTest" />');
                            gridName = this.id;
                            $('#gbox_' + gridName + ' .ui-jqgrid-htable,#' + gridName).css('width', 'inherit');
                            $('#' + gridName).parent().css('width', 'inherit');
                            var columnNames = $("#" + gridName).jqGrid('getGridParam', 'colModel');
                            var thisWidth;
                            // Loop through Cols
                            for (var itm = 0, itmCount = columnNames.length; itm < itmCount; itm++) {
                                var curObj = $('[aria-describedby=' + gridName + '_' + columnNames[itm].name + ']');
                                var thisCell = $('#' + gridName + '_' + columnNames[itm].name + ' div');
                                $('#widthTest').html(thisCell.text()).css({
                                    'font-family': thisCell.css('font-family'),
                                    'font-size': thisCell.css('font-size'),
                                    'font-weight': thisCell.css('font-weight')
                                });
                                var maxWidth = Width = $('#widthTest').width() + 24;
                                //var maxWidth = 0;
                                // Loop through Rows
                                for (var itm2 = 0, itm2Count = curObj.length; itm2 < itm2Count; itm2++) {
                                    var thisCell = $(curObj[itm2]);
                                    $('#widthTest').html(thisCell.html()).css({
                                        'font-family': thisCell.css('font-family'),
                                        'font-size': thisCell.css('font-size'),
                                        'font-weight': thisCell.css('font-weight')
                                    });
                                    thisWidth = $('#widthTest').width();
                                    if (thisWidth > maxWidth) maxWidth = thisWidth;
                                }
                                $('#' + gridName + ' .jqgfirstrow td:eq(' + itm + '), #' + gridName + '_' + columnNames[itm].name).width(maxWidth).css('min-width', maxWidth);
                            }
                            $('#widthTest').remove();
                            $("#jqgrid").jqGrid('setGridParam',{datatype:'json'});
                        }
                    })
                }
            });
    }
});

