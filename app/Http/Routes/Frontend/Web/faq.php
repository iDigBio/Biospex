<?php

// Begin Faq
$router->get('faq', [
    'uses' => 'FaqsController@index',
    'as'   => 'faq.get.index'
]);
$router->get('faq/{category}', [
    'uses' => 'FaqController@show',
    'as'   => 'faq.get.show'
]);
// End Faq