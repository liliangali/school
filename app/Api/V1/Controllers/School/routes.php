<?php

/*
 * User Controller Routes
 *
 */
$api->get('/school', 'School\SchoolController@listT')->middleware(['api.asdmin']);
$api->get('/schoola', 'School\SchoolController@listaT')->middleware(['api.asdmin']);
$api->get('/schoolt', 'School\SchoolController@tokenT');
$api->get('/schools', 'School\SchoolController@getT');
$api->post('/school', 'School\SchoolController@addT')->middleware(['api.asdmin']);
$api->post('/school/admin', 'School\SchoolController@addA')->middleware(['api.asdmin']);
$api->post('/school/admins', 'School\SchoolController@putA')->middleware(['api.asdmin']);
$api->post('/schools', 'School\SchoolController@putT')->middleware(['api.asdmin']);
$api->delete('/school', 'School\SchoolController@delT')->middleware(['api.asdmin']);
$api->delete('/school/admin', 'School\SchoolController@delA')->middleware(['api.asdmin']);
$api->get('/semesterf', 'School\SchoolController@semesterT')->middleware(['api.admin']);
$api->get('/educational', 'School\SchoolController@eduT');
//$api->put('/teacher', 'Teacher\TeacherController@putT');
