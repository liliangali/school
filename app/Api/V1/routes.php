<?php

/*
 * Dingo Api Routes
 */

$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\V1\Controllers'], function($api) {
        $dir = app_path().'/Api/V1/Controllers/' ;
        include $dir.'Auth/routes.php';
        $api->group(['middleware' => ['jwt.auth','api.admin']], function($api) use($dir) {
        });
        $api->group(['middleware' => ['jwt.auth']], function($api) use($dir) {
            include $dir.'Classes/routes.php';
            include $dir.'Teahomework/routes.php';
            include $dir.'Stuhomework/routes.php';
            include $dir.'Evalbase/routes.php';
            include $dir.'Student/routes.php';
            include $dir.'Course/routes.php';
            include $dir.'Exercise/routes.php';
            include $dir.'School/routes.php';
            include $dir.'User/routes.php';
            include $dir.'Teacher/routes.php';
        });

    });
});
