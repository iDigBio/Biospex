<?php

return [
    'default'     => 'testing',

    'connections' => [

        'setup' => array(
            'driver' => 'sqlite',
            'database' => __DIR__.'/../../database/stubdb.sqlite',
            'prefix' => '',
        ),

        'testing' => array(
            'driver' => 'sqlite',
            'database' => __DIR__.'/../../database/testdb.sqlite',
            'prefix' => '',
        ),

        'sqlite' => array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ),

        'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => 'localhost',
            'port'     => 27017,
            'username' => '',
            'password' => '',
            'database' => 'testing'
        ],

    ],

    'migrations'  => 'migrations',

];
