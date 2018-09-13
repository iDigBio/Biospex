<!-- Modal HTML -->
<div id="scorebard" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Scoreboard</h4>
            </div>
            <div class="modal-body">
                This would be the content
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('pages.close')</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        let projectId = $("#projectId").data('value');
        Echo.channel('scoreboard.' + projectId)
            .listen('ScoreBoardEvent', (e) => {
                $.each(e.data['html'], function(id,val) {
                    var str = id + ":" + val;
                    console.log(str);
                });
            });
    });
</script>