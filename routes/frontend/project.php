<?php
// Begin Public Project
$router->get('projects')->uses('ProjectsController@index')->name('projects.get.index');
$router->get('projects/{slug}')->uses('ProjectsController@project')->name('projects.get.slug');