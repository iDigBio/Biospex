<?php
$router->get('events')->uses('EventsController@index')->name('events.get.index');
$router->get('events/public/{sort?}/{order?}')->uses('EventsController@index')->name('events.public.get.sort');
$router->get('events/completed/{sort?}/{order?}')->uses('EventsController@completed')->name('events.completed.get.sort');
$router->get('events/{projects}')->uses('EventsController@project')->name('events.get.project');
$router->get('events/{uuid}/join')->uses('EventsController@eventJoin')->name('events.get.join');
$router->post('events/join/create')->uses('EventsController@eventJoinCreate')->name('events.post.join');