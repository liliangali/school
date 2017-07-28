<?php

/*
 * User Controller Routes
 *
 */
$api->get('/classes', 'Classes\ClassesController@listT');
$api->get('/classess', 'Classes\ClassesController@getT');
$api->post('/classes', 'Classes\ClassesController@addT')->middleware(['api.admin']);
$api->post('/classess', 'Classes\ClassesController@putT')->middleware(['api.admin']);
$api->delete('/classes', 'Classes\ClassesController@delT')->middleware(['api.admin']);
