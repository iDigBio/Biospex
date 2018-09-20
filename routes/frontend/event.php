<?php
$router->get('events')->uses('EventsController@index')->name('events.get.index');
$router->get('events/{uuid}/join')->uses('EventsController@eventJoin')->name('events.get.join');
$router->post('events/join/create')->uses('EventsController@eventJoinCreate')->name('events.post.join');