<?php
// Begin Public Project
$router->get('projects')->uses('ProjectsController@index')->name('front.projects.index');
$router->post('projects/sort')->uses('ProjectsController@sort')->name('front.projects.sort');
$router->get('projects/{slug}')->uses('ProjectsController@project')->name('front.projects.slug');


// Pproject Map
$router->get('projects/{project}/{state?}')->uses('ProjectsController@state')->name('front.projects.state');
