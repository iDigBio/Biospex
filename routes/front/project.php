<?php
// Begin Public Project
$router->get('projects')->uses('ProjectsController@index')->name('projects.get.index');
$router->post('projects/sort')->uses('ProjectsController@sort')->name('projects.post.sort');
$router->get('projects/{slug}')->uses('ProjectsController@project')->name('projects.get.slug');
