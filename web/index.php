<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('APP_DEBUG', false);

$app = require __DIR__ . '/app.php';

$app->run();

