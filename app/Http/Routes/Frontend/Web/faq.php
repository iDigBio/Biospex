<?php

// Begin Faq
$router->get('faq', [
    'uses' => 'FaqsController@index',
    'as'   => 'web.faqs.index'
]);
$router->get('faq/{category}', [
    'uses' => 'FaqController@show',
    'as'   => 'web.faqs.show'
]);
// End Faq