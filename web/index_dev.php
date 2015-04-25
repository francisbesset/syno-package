<?php

use Syno\Application;

require __DIR__.'/../vendor/autoload.php';

$app = new Application('dev', true);

require __DIR__.'/../src/controllers.php';
$app->run();
