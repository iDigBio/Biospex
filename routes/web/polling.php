<?php

// Ajax poll event
$router->get('poll')->uses('ServerController@poll')->name('server.get.poll');