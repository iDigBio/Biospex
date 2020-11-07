<?php
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|


Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
*/

Broadcast::channel(config('config.poll_ocr_channel'), function () {
    return true;
});

Broadcast::channel(config('config.poll_export_channel'), function () {
    return true;
});

Broadcast::channel(config('config.poll_scoreboard_channel') . '.{project}', function () {
    return true;
});

Broadcast::channel(config('config.poll_bingo_channel') . '.{bingo}', function () {
    return true;
});