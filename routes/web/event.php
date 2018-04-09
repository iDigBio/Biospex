<?php

$router->get('events/{uuid}/join')->uses('EventsController@eventJoin')->name('web.events.join');
$router->post('events/join/create')->uses('EventsController@eventJoinCreate')->name('web.events.join-create');