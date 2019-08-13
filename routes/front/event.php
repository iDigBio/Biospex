<?php
$router->get('events')->uses('EventsController@index')->name('front.events.index');
$router->post('events/sort/')->uses('EventsController@sort')->name('front.events.sort');
$router->get('events/{event}')->uses('EventsController@read')->name('front.events.read');
$router->get('events/{uuid}/signup')->uses('EventsController@signup')->name('front.events.signup');
$router->post('events/{uuid}/join')->uses('EventsController@join')->name('front.events.join');