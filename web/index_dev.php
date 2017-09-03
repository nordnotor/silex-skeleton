<?php

if (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true) || PHP_SAPI === 'cli-server')) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

error_reporting(E_ALL);

ini_set("display_errors", 1);

define('APP_DEBUG', true);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = require __DIR__ . '/app.php';

$app->register(new Sorien\Provider\PimpleDumpProvider());

$app->run();

