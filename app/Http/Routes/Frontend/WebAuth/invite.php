<?php

// Group invite routes
$router->get('groups/{groups}/invites', [
    'uses' => 'InvitesController@index',
    'as'   => 'web.invites.index'
]);

$router->post('groups/{groups}/invites', [
    'uses' => 'InvitesController@store',
    'as'   => 'web.invites.store'
]);

$router->post('groups/{groups}/invites/{invites}/resend', [
    'uses' => 'InvitesController@resend',
    'as'   => 'web.invites.resend'
]);

$router->delete('groups/{groups}/invites/{invites}', [
    'uses' => 'InvitesController@delete',
    'as'   => 'web.invites.delete'
]);
