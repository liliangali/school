<?php

/*
 * User Controller Routes
 *
 */
$api->get('/student', 'Student\StudentController@listT');
$api->post('/student', 'Student\StudentController@addT');
$api->post('/students', 'Student\StudentController@putT');
$api->post('/studentc', 'Student\StudentController@changeT');
$api->delete('/student', 'Student\StudentController@delT');
//$api->put('/teacher', 'Teacher\TeacherController@putT');
