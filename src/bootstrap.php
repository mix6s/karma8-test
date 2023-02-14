<?php

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/functions.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');