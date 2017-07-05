<?php

/*
 * User Controller Routes
 *
 */
$api->get('/teacher', 'Teacher\TeacherController@listT');
$api->post('/teacher', 'Teacher\TeacherController@addT');
$api->delete('/teacher', 'Teacher\TeacherController@delT');
$api->post('/teachers', 'Teacher\TeacherController@putT');
