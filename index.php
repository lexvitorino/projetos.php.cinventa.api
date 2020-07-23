<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");

session_start();

require 'config.php';
require 'routers.php';
require 'vendor/autoload.php';

$core = new \Core\Core();
$core->run();
