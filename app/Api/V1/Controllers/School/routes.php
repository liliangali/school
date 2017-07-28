<?php

/*
 * User Controller Routes
 *
 */
$api->get('/school', 'School\SchoolController@listT');
$api->get('/schoola', 'School\SchoolController@listaT');
$api->get('/schoolt', 'School\SchoolController@tokenT');
$api->get('/schools', 'School\SchoolController@getT');
$api->post('/school', 'School\SchoolController@addT');
$api->post('/school/admin', 'School\SchoolController@addA');
$api->post('/school/admins', 'School\SchoolController@putA');
$api->post('/schools', 'School\SchoolController@putT');
$api->delete('/school', 'School\SchoolController@delT');
$api->delete('/school/admin', 'School\SchoolController@delA');
//$api->put('/teacher', 'Teacher\TeacherController@putT');
