$(document).ready(function() {

    $('.collapse').on('shown.bs.collapse', function () {
        $("#collapse"+this.id).removeClass("glyphicon-folder-close").addClass("glyphicon-folder-open");
        $("#"+this.id).load("/projects/"+this.id+"/expeditions");
    });

    $('.collapse').on('hidden.bs.collapse', function () {
        $("#collapse"+this.id).removeClass("glyphicon-folder-open").addClass("glyphicon-folder-close");
        $( "#expeditions"+this.id).html('');
    });

    $('#add_target').on('click', function() {
        if ($('div.target:first').is(":hidden") ) {
            $('div.target:first').show();
        } else {
            $('div.target:last').after($('div.target:last').clone()
                .find(':input')
                .each(function(){
                    this.name = this.name.replace(/\[(\d+)\]/, function(str,p1){
                        return '[' + (parseInt(p1,10)+1) + ']';
                    });
                })
                .end());
        }
        $('#targetCount').val($("div.target:visible").length);
    });
    $('#remove_target').click(function() {
        if ($('div.target').length == 1) {
            $('div.target').hide();
        } else {
            $('div.target:last').remove();
        }
        $('#targetCount').val($("div.target:visible").length);
    });

    $( "#formAddData" ).validate({
        rules: {
            file: {
                required: true,
                extension: "zip"
            }
        }
    });

    /*
    $('input[name="user"]').change(function(){
        $('input[class="userperm"]:checkbox').prop('checked', this.checked);
     });
    $('input[name="group"]').change(function(){
        $('input[class="groupperm"]:checkbox').prop('checked', this.checked);
    });
    $('input[name="project"]').change(function(){
        $('input[class="projectperm"]:checkbox').prop('checked', this.checked);
    });
    $('input[name="navigation"]').change(function(){
        $('input[class="navigationperm"]:checkbox').prop('checked', this.checked);
    });
    $('input[name="permission"]').change(function(){
        $('input[class="permissionperm"]:checkbox').prop('checked', this.checked);
    });
    */

    var $grid = $("#jqgrid");
    var projectId = $("#projectId").val();
    var expeditionId = $("#expeditionId").val();
    $grid.jqGrid({
        mtype: "GET",
        url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grids",
        datatype: "json",
        colModel:[
            {name: '_id',index: '_id', hidden: true, editable: true, editrules: {edithidden: true}, jsonmap: "cell.0"},
            {name: 'ocr',index: 'ocr', sortable:false, jsonmap: "cell.3"},
            {name: 'id',index: 'id', sortable:false, jsonmap: "cell.1"},
            {name: 'accessURI', index: 'accessURI', editable: false, sortable: false, formatter: 'showlink',
                formatoptions: { baseLinkUrl: 'javascript:', showAction: "link('", addParam: "');"},
                jsonmap: "cell.2"
            },
        ],
        rowNum: 10,
        rowList: [5,10,20],
        pager: "#jqpager",
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
        jsonReader: {
            root: "rows",
            page: "page",
            total: "total",
            records: "records"
        },
        loadBeforeSend: function(jqXHR) {
            jqXHR.setRequestHeader("X-CSRF-Token", $('meta[name="_token"]').attr('content'));
        },
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
        }
    });


/*
    if($('#grid').length >0 ){
        var projectId = $("#projectId").val();
        var expeditionId = $("#expeditionId").val();

        $.ajax({
            type: "POST",
            url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grid",
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function (response) {
                var obj = response.GetInfoResult;
                var cnames = JSON.parse(obj.ColNames);
                var cmodel = JSON.parse(obj.ColModel);

                $("#tblInfo").empty().jqGrid({
                    datatype: "local",
                    height: 200,
                    colNames: cnames,
                    colModel: cmodel,
                    width: 500,
                    pager: "#pager",
                    rowList: [10, 20, 30],
                    rowNum: 10,
                    emptyrecords: "No records to view",
                    sortorder: "asc",
                    viewrecords: true,
                    loadtext: "Loading....",
                    sortable: true
                });

                var mydata = JSON.parse(JSON.parse(obj.ColData).Row);
                for (dr in mydata) {
                    $("#tblInfo").jqGrid('addRowData', dr, mydata[dr]);
                }
            },
            error: function (response)
            { alert(response.responseText); }
        });
        return false;
    }
*/
});

function link(id) {

    var row = id.split("=");
    var row_ID = row[1];
    var uri= $("#jqgrid").getCell(row_ID, 'accessURI');
    window.open(uri);

}
