<?php

// Contact form
$router->get('contact', [
    'uses' => 'HomeController@getContact',
    'as'   => 'home.get.contact'
]);

$router->post('contact', [
    'uses' => 'HomeController@postContact',
    'as'   => 'home.post.contact'
]);
// End Contact form

// Home and Welcome
$router->get('/', [
    'uses' => 'HomeController@index',
    'as'   => 'home'
]);
// End Home and Welcome

// Begin Vision
$router->get('ourvision', [
    'uses' => 'HomeController@vision',
    'as'   => 'home.get.vision'
]);
// End Vision

// Begin About
$router->get('team', [
    'uses' => 'HomeController@team',
    'as'   => 'home.get.team'
]);
// End Team

// Begin Project Slug
$router->get('project/{slug}', [
    'uses' => 'HomeController@project',
    'as'   => 'home.get.project'
]);
// End Project Slug