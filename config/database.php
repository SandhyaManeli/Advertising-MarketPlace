<?php
return [
    
    'default' => 'accounts',
    'migrations' => 'migrations',
    'connections' => [
        'accounts' => [
            'name'      => 'accounts',
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'accounts',
            'username'  => 'root',
            #'password'  => 'Billdesk@ptg',
            'password'  => 'Amp@ptg123$%',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],
        'content' => [
            'name'      => 'content',
            'driver'    => 'mongodb',
            'host'      => 'localhost',
            'database'  => 'content',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
            'table' => 'users',
        ],
    ],
];
