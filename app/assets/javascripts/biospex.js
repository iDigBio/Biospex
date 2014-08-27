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

    $("[data-toggle='popover']").popover();

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
/*
    if($('#list').length >0 ){
        var groupId = $("#groupId").val();
        var projectId = $("#projectId").val();
        var expeditionId = $("#expeditionId").val();

        $.ajax({
            type: "POST",
            url: "groups/"+groupId+"/projects/"+projectId+"/expeditions/"+expeditionId+"/loadjq",
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
