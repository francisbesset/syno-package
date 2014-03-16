<?php

$app->mount('/admin', require 'admin_controller.php');
$app->mount('/', require 'front_controller.php');
