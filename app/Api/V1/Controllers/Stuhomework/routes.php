<?php

/*
 * User Controller Routes
 *
 */

$api->get('/teahomeworka', 'Stuhomework\StuhomeworkController@listAT')->middleware(['api.student']);
$api->get('/stuhomework', 'Stuhomework\StuhomeworkController@getT')->middleware(['api.student']);
$api->get('/tstuhomework', 'Stuhomework\StuhomeworkController@gettT');
$api->get('/classwork', 'Stuhomework\StuhomeworkController@classT');

$api->post('/stuhomework', 'Stuhomework\StuhomeworkController@addT')->middleware(['api.student']);
$api->post('/stuhp', 'Stuhomework\StuhomeworkController@hpT')->middleware(['api.student']);
$api->post('/stupf', 'Stuhomework\StuhomeworkController@pfT')->middleware(['api.teacher']);
$api->post('/stuzpf', 'Stuhomework\StuhomeworkController@zpfT')->middleware(['api.teacher']);