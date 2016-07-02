<?php

// Begin NfN
$router->get('nfn', [
    'uses' => 'NfNAppController@index',
    'as'   => 'web.nfn.index'
]);
// End NfN