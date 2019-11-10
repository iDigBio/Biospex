<?php
// Begin Public Project
$router->get('projects')->uses('ProjectsController@index')->name('front.projects.index');
$router->post('projects/sort')->uses('ProjectsController@sort')->name('front.projects.sort');
// Redirect old links to new
$router->get('project/{slug}', function($slug) {
    return redirect("/projects/$slug", 301);
});
$router->get('projects/{slug}')->uses('ProjectsController@project')->name('front.projects.slug');
$router->get('projects/{project}/chart-image')->uses('ProjectsController@chartImage')->name('front.projects.image');

$router->get('projects/{project}/test')->uses('ProjectsController@test')->name('front.projects.test');
$router->get('projects/{project}/transcriptions/{year}')->uses('TranscriptionsController@index')->name('front.projects.transcriptions');

// Project Map
$router->get('projects/{project}/{state?}')->uses('ProjectsController@state')->name('front.projects.state');

