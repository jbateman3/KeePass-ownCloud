<?php
return [
    'resources' => [
        'password' => ['url' => '/passwords'],
        'password_api' => ['url' => '/api/0.1/passwords']
    ],
    'routes' => [
	['name' => 'password#validatePath', 'url' => '/password', 'verb' => 'POST'],
	['name' => 'password#indexNew', 'url' => '/passwords', 'verb' => 'POST'],
        ['name' => 'settings#set', 'url' => '/settings/{setting}/{value}', 'verb' => 'POST'],
        ['name' => 'settings#setadmin', 'url' => '/settings/{setting}/{value}/{admin1}/{admin2}', 'verb' => 'POST'],
        ['name' => 'settings#get', 'url' => '/settings', 'verb' => 'GET'],
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'password_api#preflighted_cors', 'url' => '/api/0.1/{path}',
         'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']]
    ]
];
