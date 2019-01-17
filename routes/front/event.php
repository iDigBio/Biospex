<?php
$router->get('events')->uses('EventsController@index')->name('front.events.index');
$router->post('events/sort/')->uses('EventsController@sort')->name('front.events.sort');
$router->get('events/{uuid}/join')->uses('EventsController@eventJoin')->name('front.events.join');
$router->post('events/join/create')->uses('EventsController@eventJoinCreate')->name('front.events.create');