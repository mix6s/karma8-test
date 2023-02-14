<?php

require_once __DIR__ . '/bootstrap.php';

$faker = Faker\Factory::create();

$dbConnection = createDBConnection();

$dbConnection->exec('TRUNCATE users');

$sqlValues = [];
$values = [];
for ($i = 0; $i < 100_000; $i++) {
    $values[] = $faker->unique()->userName();
    $values[] = $faker->unique()->email();
    $values[] = random_int(time() - 60 * 60 * 24 * 10, time() + 60 * 60 * 24 * 5);
    $values[] = random_int(0, 1);
    $sqlValues[] = '(?, ?, ?, ?)';
    if ($i % 5000 === 0) {
        $stmt = $dbConnection->prepare(
            'INSERT INTO users (username, email, validts, confirmed) VALUES ' . implode(', ', $sqlValues)
        );
        $stmt->execute($values);
        $values = [];
        $sqlValues = [];
        echo "Inserted 5000 rows, total: " . $i . PHP_EOL;
    }
}