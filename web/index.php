<?php

use Syno\Application;

require __DIR__.'/../vendor/autoload.php';

$app = new Application('prod', false);

require __DIR__.'/../src/controllers.php';
$app->run();
