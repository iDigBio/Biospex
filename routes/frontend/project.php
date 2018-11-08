<?php
// Begin Public Project
$router->get('projects')->uses('ProjectsController@index')->name('projects.get.index');
$router->get('projects/page/{slug}')->uses('ProjectsController@project')->name('projects.get.slug');
$router->get('projects/public/{sorting?}')->uses('ProjectsController@index')->name('projects.get.sort');
