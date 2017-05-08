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
$router->get('/project-list/{count?}', [
    'uses' => 'HomeController@projects',
    'as'   => 'home.project-list'
]);
// End Home and Welcome

// Begin Vision
$router->get('ourvision', [
    'uses' => 'HomeController@vision',
    'as'   => 'home.get.vision'
]);
// End Vision

// Begin Project Slug
$router->get('project/{slug}', [
    'uses' => 'HomeController@project',
    'as'   => 'home.get.project'
]);
// End Project Slug

// Begin Project Slug
$router->get('project/{project}/chart', [
    'uses' => 'HomeController@loadAmChart',
    'as'   => 'home.get.chart'
]);
// End Project Slug