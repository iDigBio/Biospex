<?php
// Begin Public Project
$router->get('projects')->uses('ProjectsController@index')->name('front.projects.index');
$router->post('projects/sort')->uses('ProjectsController@sort')->name('front.projects.sort');
$router->get('projects/{slug}')->uses('ProjectsController@project')->name('front.projects.slug');
