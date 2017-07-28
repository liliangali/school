<?php

/*
 * User Controller Routes
 *
 */

$api->get('/evalbase', 'Evalbase\EvalbaseController@listT')->middleware(['api.teacher']);
$api->post('/evalbase', 'Evalbase\EvalbaseController@addT')->middleware(['api.teacher']);
$api->post('/estandard', 'Evalbase\EvalbaseController@saddT')->middleware(['api.teacher']);
$api->post('/studf', 'Evalbase\EvalbaseController@dfT')->middleware(['api.teacher']);
