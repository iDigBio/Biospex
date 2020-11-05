<?php

// Group invite routes
$router->get('groups/{groups}/invites')->uses('InvitesController@index')->name('admin.invites.index');
$router->post('groups/{groups}/invites')->uses('InvitesController@store')->name('admin.invites.store');
$router->delete('groups/{groups}/invites/{invites}')->uses('InvitesController@delete')->name('admin.invites.delete');