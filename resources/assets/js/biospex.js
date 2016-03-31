$(document).ready(function() {

    $('.toggle').on('click', function () {
        if ($(this).hasClass('fa-folder')) {
            $(this).removeClass("fa-folder").addClass("fa-folder-open");
            var curRow = $(this).closest('tr');
            var newRow = '<tr class="ajax-rows"><td></td><td colspan="4"><span id="row'+this.id+'"></span></td></tr>';
            curRow.after(newRow);
            $("#row"+this.id).load("/projects/"+this.id+"/expeditions");
        } else {
            $(this).removeClass("fa-folder-open").addClass("fa-folder");
            $(this).closest('tr').next('tr').remove();
        }
    });

    $(".table-sort").tablesorter({
        // this will apply the bootstrap theme if "uitheme" widget is included
        // the widgetOptions.uitheme is no longer required to be set
        theme : "bootstrap",

        widthFixed: false,

        headerTemplate : '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!

        // widget code contained in the jquery.tablesorter.widgets.js file
        // use the zebra stripe widget if you plan on hiding any rows (filter widget)
        widgets : [ "uitheme", "zebra" ],

        widgetOptions : {
            // using the default zebra striping class name, so it actually isn't included in the theme variable above
            // this is ONLY needed for bootstrap theming if you are using the filter widget, because rows are hidden
            zebra : ["even", "odd"],
            // reset filters button
            filter_reset : ".reset",
            // extra css class name (string or array) added to the filter element (input or select)
            filter_cssFilter: "form-control",
        }
    });

    $(".table-sort").bind("sortStart",function() {
        $('.ajax-rows').remove();
        $('.toggle').removeClass("fa-folder-open").addClass("fa-folder");
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

    $( "#form-data" ).validate({
        rules: {
            dwc: {
                required: true,
                extension: "zip"
            }
        }
    });
    $( "#form-recordset" ).validate({
        rules: {
            recordset: {
                required: true
            }
        }
    });
    $( "#form-data-url" ).validate({
        rules: {
            "data-url": {
                required: true
            }
        }
    });
    $( "#form-trans" ).validate({
        rules: {
            transcription: {
                required: true,
                extension: "csv"
            }
        }
    });


    $("#userGroup").change(function(){
        this.value == 'new' ? $("#groupInput").show() : $("#groupInput").hide();
    });
    if ($("#userGroup").length > 0){
        $("#userGroup").val() == 'new' ? $("#groupInput").show() : $("#groupInput").hide();
    }

    $('#selectall').click(function(event) {  //on click
        if(this.checked) { // check select status
            $('.checkbox-all').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"
            });
        }else{
            $('.checkbox-all').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"
            });
        }
    });

    $('#processes').click(function() {
        $('#processModal')
            .prop('class', 'modal fade') // revert to default
            .addClass('left');
        $('#processModal').modal('show');
    });

});

$(function(){
    $('[data-method]').not(".disabled").append(function(){
            var methodForm = "\n"
            methodForm += "<form action='"+$(this).attr('href')+"' method='POST' style='display:none'>\n"
            methodForm += " <input type='hidden' name='_method' value='"+$(this).attr('data-method')+"'>\n"
            if ($(this).attr('data-token'))
            {
                methodForm +="<input type='hidden' name='_token' value='"+$(this).attr('data-token')+"'>\n"
            }
            methodForm += "</form>\n"
            return methodForm
        })
        .removeAttr('href')
        .attr('onclick',' if ($(this).hasClass(\'action_confirm\')) { if(confirm("Are you sure you want to do this?")) { $(this).find("form").submit(); } } else { $(this).find("form").submit(); }');
});