<?php

return array(
    'default' => 'testing',

    'connections' => array(

        'setup' => array(
            'driver' => 'sqlite',
            'database' => __DIR__.'/../../database/stubdb.sqlite',
            'prefix' => '',
            'username'  => 'root',
            'password'  => 'password',
        ),

        'testing' => array(
            'driver' => 'sqlite',
            'database' => __DIR__.'/../../database/testdb.sqlite',
            'prefix' => '',
            'username'  => 'root',
            'password'  => 'password',
        ),

        'sqlite' => array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'username'  => 'root',
			'password'  => 'password',
        ),

        'mysql' => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'biospex',
            'username'  => 'root',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),

        'mongodb' => array(
            'driver'   => 'mongodb',
            'host'     => 'localhost',
            'port'     => 27017,
            'username' => 'biospex',
            'password' => 'biospex',
            'database' => 'biospex'
        ),

    ),

    'migrations' => 'migrations',

);