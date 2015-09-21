<?php

return [
    'default'     => 'testing',

    'connections' => [

        'setup'   => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../../database/stubdb.sqlite',
            'prefix'   => '',
            'username' => 'root',
            'password' => 'password',
        ],

        'testing' => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../../database/testdb.sqlite',
            'prefix'   => '',
            'username' => 'root',
            'password' => 'password',
        ],

        'sqlite'  => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'username' => 'root',
            'password' => 'password',
        ],

        'mysql'   => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'biospex',
            'username'  => 'root',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],

        'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => 'localhost',
            'port'     => 27017,
            'username' => 'biospex',
            'password' => 'biospex',
            'database' => 'biospex'
        ],

    ],

    'migrations'  => 'migrations',

];
