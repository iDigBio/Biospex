<?php

// Begin Faq
$router->get('faq', [
    'uses' => 'FaqsController@index',
    'as'   => 'web.faqs.index'
]);
$router->get('faq/{category}', [
    'uses' => 'FaqsController@show',
    'as'   => 'web.faqs.show'
]);
// End Faq