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

    $( "#form-data" ).validate({
        rules: {
            file: {
                required: true,
                extension: "nfn"
            }
        }
    });
    $( "#form-trans" ).validate({
        rules: {
            file: {
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
});
