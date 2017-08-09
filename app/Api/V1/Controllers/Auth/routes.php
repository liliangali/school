<?php

/*
 * Auth Controller Routes
 *
 */

$api->post('/auth/login', 'Auth\AuthController@authenticate');
$api->post('/auth/register', 'Auth\AuthController@register');
$api->any('/rep', 'Auth\AuthController@rep');
$api->any('/ureset', 'Auth\AuthController@reset');
$api->any('/getF', 'Auth\AuthController@getF');
