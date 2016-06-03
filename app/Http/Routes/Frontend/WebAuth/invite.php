<?php

// Group invite routes
$router->get('groups/{groups}/invites', [
    'uses' => 'InvitesController@index',
    'as'   => 'invites.get.index'
]);

$router->post('groups/{groups}/invites', [
    'uses' => 'InvitesController@store',
    'as'   => 'invites.post.store'
]);

$router->post('groups/{groups}/invites/{invites}/resend', [
    'uses' => 'InvitesController@resend',
    'as'   => 'invites.post.resend'
]);

$router->delete('groups/{groups}/invites/{invites}', [
    'uses' => 'InvitesController@delete',
    'as'   => 'invites.delete.delete'
]);
