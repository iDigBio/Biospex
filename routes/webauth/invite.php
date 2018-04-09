<?php

// Group invite routes
$router->get('groups/{groups}/invites')->uses('InvitesController@index')->name('webauth.invites.index');
$router->post('groups/{groups}/invites')->uses('InvitesController@store')->name('webauth.invites.store');
$router->post('groups/{groups}/invites/{invites}/resend')->uses('InvitesController@resend')->name('webauth.invites.resend');
$router->delete('groups/{groups}/invites/{invites}')->uses('InvitesController@delete')->name('webauth.invites.delete');