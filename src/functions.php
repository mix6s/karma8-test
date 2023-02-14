<?php

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPStreamConnection;

function check_email(string $email): int
{
    sleep(random_int(1, 1));
    return random_int(0, 1);
}

function send_email($email, $from, $to, $subj, $body): void
{
    sleep(random_int(1, 1));
}

function createAmqpConnection(): AMQPStreamConnection
{
    return new AMQPStreamConnection($_ENV['AMQP_HOST'], $_ENV['AMQP_PORT'], $_ENV['AMQP_USER'], $_ENV['AMQP_PASSWORD']);
}

function createLogger(): Logger
{
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../var/app.log', Level::Info));
    return $log;
}

function createDBConnection(): PDO
{
    $dbhost = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_DATABASE'];
    $dbuser = $_ENV['DB_USER'];
    $dbpass = $_ENV['DB_PASSWORD'];

    return new PDO("pgsql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
}