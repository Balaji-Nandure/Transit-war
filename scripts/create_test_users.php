<?php
// Script to create users from Phase2.csv

$pdo = require __DIR__ . '/../config/db.php';

$csvPath = __DIR__ . '/../Phase2.csv';

if (!file_exists($csvPath)) {
    fwrite(STDERR, "CSV file not found: {$csvPath}\n");
    exit(1);
}

$handle = fopen($csvPath, 'r');
if ($handle === false) {
    fwrite(STDERR, "Unable to open CSV file: {$csvPath}\n");
    exit(1);
}

$headers = fgetcsv($handle);
if ($headers === false) {
    fclose($handle);
    fwrite(STDERR, "CSV file is empty: {$csvPath}\n");
    exit(1);
}

$headerMap = array_flip(array_map('trim', $headers));
$requiredColumns = ['username', 'email', 'password'];

foreach ($requiredColumns as $column) {
    if (!isset($headerMap[$column])) {
        fclose($handle);
        fwrite(STDERR, "Missing required CSV column: {$column}\n");
        exit(1);
    }
}

$insertStmt = $pdo->prepare(
    'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
);
$existingStmt = $pdo->prepare(
    'SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1'
);

$created = 0;
$skipped = 0;
$lineNumber = 1;

while (($row = fgetcsv($handle)) !== false) {
    $lineNumber++;

    $username = trim($row[$headerMap['username']] ?? '');
    $email = trim($row[$headerMap['email']] ?? '');
    $password = trim($row[$headerMap['password']] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        echo "Skipped row {$lineNumber}: missing username/email/password\n";
        $skipped++;
        continue;
    }

    try {
        $existingStmt->execute([$username, $email]);
        if ($existingStmt->fetch()) {
            echo "Skipped existing user: {$username}\n";
            $skipped++;
            continue;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt->execute([$username, $email, $passwordHash]);
        echo "Created user: {$username}\n";
        $created++;
    } catch (Exception $e) {
        echo "Failed row {$lineNumber} ({$username}): {$e->getMessage()}\n";
        $skipped++;
    }
}

fclose($handle);

echo "Done. Created: {$created}, Skipped: {$skipped}\n";