<?php

/*
 * User Controller Routes
 *
 */
$api->get('/classes', 'Classes\ClassesController@listT');
$api->get('/classess', 'Classes\ClassesController@getT');
$api->post('/classes', 'Classes\ClassesController@addT');
$api->post('/classess', 'Classes\ClassesController@putT');
$api->delete('/classes', 'Classes\ClassesController@delT');
//$api->put('/teacher', 'Teacher\TeacherController@putT');
