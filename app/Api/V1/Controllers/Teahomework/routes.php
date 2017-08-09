<?php

/*
 * User Controller Routes
 *
 */
$api->get('/teahomework', 'Teahomework\TeahomeworkController@listT');
$api->get('/gteahomework', 'Teahomework\TeahomeworkController@getT');
$api->post('/teahomework', 'Teahomework\TeahomeworkController@addT')->middleware(['api.teacher']);
