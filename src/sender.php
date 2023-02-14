<?php

use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/bootstrap.php';


$fromTs = time() + EXPIRE_TIME ;
$toTs = time() + EXPIRE_TIME + (int) $_ENV['EXPIRE_TIME_INTERVAL'];
$offset = 0;

$connection = createDBConnection();
$amqpConnection = createAmqpConnection();
$channel = $amqpConnection->channel();
$channel->queue_declare($_ENV['QUEUE_NAME'], false, false, false, false);
$logger = createLogger();

$stmt = $connection->prepare(
    'SELECT u.username, u.email, u.validts
FROM users AS u
WHERE u.confirmed = :status_confirmed AND u.validts >= :from_ts AND u.validts <= :to_ts
AND NOT EXISTS(SELECT 1 FROM sent_notifications AS sn WHERE sn.username = u.username AND sn.validts = u.validts)
ORDER BY u.validts
LIMIT :limit 
OFFSET :offset'
);

do {
    $stmt->execute([
        'status_confirmed' => STATUS_CONFIRMED,
        'from_ts' => $fromTs,
        'to_ts' => $toTs,
        'offset' => $offset,
        'limit' => LIMIT
    ]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        try {
            $insertStmt = $connection->prepare(
                'INSERT INTO sent_notifications (username, validts) VALUES (:username, :validts)'
            );
            $insertStmt->execute([
                ':username' => $row['username'],
                ':validts' => $row['validts']
            ]);
        } catch (PDOException $e) {
            $logger->error('Failed to insert notification: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'username' => $row['username'],
                'validts' => $row['validts']
            ]);
            continue;
        }


        $msg = new AMQPMessage(json_encode(['username' => $row['username'], 'email' => $row['email']]));
        $channel->basic_publish($msg, '', $_ENV['QUEUE_NAME']);
        $logger->info('Message published', [
            'username' => $row['username'],
            'email' => $row['email']
        ]);
    }

    $offset += LIMIT;
} while (count($rows) > 0);

$channel->close();
$amqpConnection->close();
$connection = null;
