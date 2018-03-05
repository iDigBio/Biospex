<?php

$router->get('events/{groups}/join')->uses('EventsController@eventJoin')->name('webauth.events.join');
$router->post('events/{groups}/join')->uses('EventsController@eventJoinStore')->name('webauth.events.joinStore');