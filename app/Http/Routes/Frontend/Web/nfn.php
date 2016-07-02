<?php

// Begin NfN
$router->get('nfn', [
    'uses' => 'NfnAppController@index',
    'as'   => 'web.nfn.index'
]);
// End NfN