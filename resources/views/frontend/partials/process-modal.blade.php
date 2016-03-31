<div class="modal fade" id="processModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                @if (Auth::check())
                    <div id="noProcess">No processes running at this time.</div>
                    @foreach(Auth::getUser()->groups()->get() as $group)
                        <p id="channel-{{ $group->id }}"></p>
                    @endforeach
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<a class='btn btn-primary btn' id="processes" data-direction='left'>Processes</a>
<script src="http://192.168.56.20:3000/socket.io/socket.io.js"></script>
@if (Auth::check())
    <script>
        var socket = io('http://192.168.56.20:3000');
        @foreach(Auth::getUser()->groups()->get() as $group)
            socket.on("channel-{{ $group->id }}:app.polling", function (message) {
            if (jQuery.isEmptyObject(message)) {
                return;
            }
            $("#noProcess").text('');
            $.each(data, function (index) {
                var html = data[index].projectTitle + "<br />" +
                        "There are " + data[index].totalSubjectsAhead + " records in the queue before yours.<br />" +
                        data[index].groupSubjectRemaining + " out of " + data[index].groupSubjectCount + "remaining to be processed";
                $("#channel-" + data[index].groupId).text(html);
            });
        });
        @endforeach
    </script>
@endif