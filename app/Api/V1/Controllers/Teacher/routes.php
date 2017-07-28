<?php

/*
 * User Controller Routes
 *
 */
$api->get('/teacher', 'Teacher\TeacherController@listT');
$api->get('/teachers', 'Teacher\TeacherController@getT');
$api->post('/teacher', 'Teacher\TeacherController@addT');
$api->delete('/teacher', 'Teacher\TeacherController@delT');
$api->post('/teachers', 'Teacher\TeacherController@putT');
$api->post('/upt', 'Teacher\TeacherController@upT');
