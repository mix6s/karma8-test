<?php

require_once __DIR__ . '/bootstrap.php';

$connection = createDBConnection();
$amqpConnection = createAmqpConnection();
$channel = $amqpConnection->channel();
$channel->queue_declare($_ENV['QUEUE_NAME'], false, false, false, false);
$logger = createLogger();

$callback = function ($msg) use ($connection, $logger) {
    $logger->info('Message received' . $msg->body);
    $message = json_decode($msg->body, true);
    $username = $message['username'];
    $email = $message['email'];

    $stmt = $connection->prepare('SELECT email, checked, valid, last_check FROM emails WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch();
    $exist = true;
    if (!is_array($row)) {
        $row = [
            'email' => $email,
            'checked' => 0,
            'valid' => 0,
            'last_check' => null,
        ];
        $exist = false;
    }

    if ($row['checked'] === 0
        || $row['last_check'] === null
        || $row['last_check'] < (time() - (int) $_ENV['EMAIL_LAST_CHECK_EXPIRE_PERIOD'])) {
        $row['checked'] = 1;
        $row['valid'] = check_email($email);
        $row['last_check'] = time();
        $stmt = $connection->prepare(
            $exist
                ? 'UPDATE emails SET checked = 1, valid = :is_valid, last_check = :last_check WHERE email = :email'
                : 'INSERT INTO emails (email, checked, valid, last_check) VALUES (:email, 1, :is_valid, :last_check) ON CONFLICT DO NOTHING'
        );
        $stmt->execute([
            ':email' => $row['email'],
            ':is_valid' => $row['valid'],
            ':last_check' => $row['last_check']
        ]);
        $logger->info('Email checked ' . $email . ' with result ' . $row['valid']);
    }

    if ($row['valid'] === 0) {
        return;
    }

    $subj = "{$username}, your subscription is expiring soon";
    send_email($email, $_ENV['FROM'], $email, $subj, $subj);
    $logger->info('Email sent to ' . $email);
};

$channel->basic_consume($_ENV['QUEUE_NAME'], '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}
