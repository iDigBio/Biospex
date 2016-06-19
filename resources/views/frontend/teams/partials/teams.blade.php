<div class="col-md-4">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h5 class="panel-user-username">{{ $team->full_name }}</h5>
            <h6 class="panel-user-email">{{ Html::mailto($team->email, $team->email) }}</h6>
        </div>
        <div class="panel-body">
            <strong>Institution: </strong>{{ $team->institution }}
        </div>
    </div>
</div>