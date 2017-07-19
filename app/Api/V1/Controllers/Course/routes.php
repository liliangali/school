<?php

/*
 * User Controller Routes
 *
 */
$api->get('/course', 'Course\CourseController@listT');
$api->get('/coursei', 'Course\CourseController@listIT');
$api->get('/coursea', 'Course\CourseController@listAT');
$api->get('/courses', 'Course\CourseController@listST');
$api->post('/course', 'Course\CourseController@addT');
$api->post('/courses', 'Course\CourseController@putT');
$api->delete('/course', 'Course\CourseController@delT');
