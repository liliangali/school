<?php

/*
 * User Controller Routes
 *
 */
$api->get('/exlistt', 'Exercise\ExerciseController@listT');
$api->get('/gett', 'Exercise\ExerciseController@getT');
$api->get('/exlistst', 'Exercise\ExerciseController@listST');
$api->get('/exgetst', 'Exercise\ExerciseController@getST');
$api->get('/exgetat', 'Exercise\ExerciseController@getAT');
$api->get('/exer', 'Exercise\ExerciseController@listT');
$api->get('/exer', 'Exercise\ExerciseController@listT');
$api->get('/exergeti', 'Exercise\ExerciseController@getIT');