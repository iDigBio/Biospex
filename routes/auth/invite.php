<?php

// Group invite routes
$router->get('groups/{groups}/invites')->uses('InvitesController@index')->name('web.invites.index');
$router->post('groups/{groups}/invites')->uses('InvitesController@store')->name('web.invites.store');
$router->post('groups/{groups}/invites/{invites}/resend')->uses('InvitesController@resend')->name('web.invites.resend');
$router->delete('groups/{groups}/invites/{invites}')->uses('InvitesController@delete')->name('web.invites.delete');