<?php

/*
 * User Controller Routes
 *
 */
$api->get('/course', 'Course\CourseController@listT');
$api->post('/course', 'Course\CourseController@addT');
$api->post('/courses', 'Course\CourseController@putT');
$api->delete('/course', 'Course\CourseController@delT');
