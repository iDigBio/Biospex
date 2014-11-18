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

    var $grid = $("#grid");
    var projectId = $("#projectId").val();
    var expeditionId = $("#expeditionId").val();
    $grid.jqGrid({
        mtype: "POST",
        url: "/projects/"+projectId+"/expeditions/"+expeditionId+"/grid",
        postData: {projectId:projectId, expeditionId:expeditionId },
        datatype: "json",
        colModel: [{ name: "id", width: 50 },
            { name: "accessUri", width: 80, align: "center", formatter: "string"},
            { name: "orc", width: 100, formatter: "string", align: "right"}
        ],
        rowNum: 10,
        rowList: [5,10,20],
        pager: "#pager",
        gridview: true,
        rownumbers: true,
        sortname: "id",
        viewrecords: true,
        sortorder: "desc",
        caption: "Setting coloumn headers dynamicaly",
        jsonReader: { root: "data" },
        loadBeforeSend: function(jqXHR) {
            jqXHR.setRequestHeader("X-CSRF-Token", $('meta[name="_token"]').attr('content'));
        },
        beforeProcessing: function (data) {
            var $self = $(this), model = data.model, name, $colHeader, $sortingIcons;
            if (model) {
                for (name in model) {
                    if (model.hasOwnProperty(name)) {
                        $colHeader = $("#jqgh_" + $.jgrid.jqID(this.id + "_" + name));
                        $sortingIcons = $colHeader.find(">span.s-ico");
                        $colHeader.text(model[name].label);
                        $colHeader.append($sortingIcons);
                    }
                }
            }
        },
        loadonce: true,
        height: "auto"
    });
    $("#en").button().click(function () {
        $grid.jqGrid("setGridParam", {
            datatype: "json",
            url: "DynamicHeaderProperties.json"
        }).trigger("reloadGrid", {current: true});
    });
    $("#ru").button().click(function () {
        $grid.jqGrid("setGridParam", {
            datatype: "json",
            url: "DynamicHeaderPropertiesRu.json"
        }).trigger("reloadGrid", {current: true});
    });
    $("#de").button().click(function () {
        $grid.jqGrid("setGridParam", {
            datatype: "json",
            url: "DynamicHeaderPropertiesDe.json"
        }).trigger("reloadGrid", {current: true});
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
