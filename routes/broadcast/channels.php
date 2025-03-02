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
*/

use App\Models\BingoUser;
use Illuminate\Support\Facades\Broadcast;

/*
Broadcast::routes([
    'middleware' => ['web', 'authenticate-bingoUser'],
]);
*/

Broadcast::channel(config('config.poll_bingo_channel').'.{bingo}', function (BingoUser $bingoUser) {
    return true;
    // return ['id' => $bingoUser->id, 'uuid' => $bingoUser->uuid];
});

Broadcast::channel(config('config.poll_ocr_channel'), function () {
    return true;
});

Broadcast::channel(config('config.poll_export_channel'), function () {
    return true;
});

Broadcast::channel(config('config.poll_scoreboard_channel').'.{project}', function () {
    return true;
});

Broadcast::channel(config('config.poll_wedigbio_progress_channel').'.{date}', function () {
    return true;
});
