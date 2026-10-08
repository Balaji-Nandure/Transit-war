<?php
// Database connection for Transit-war
$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'transactiwar';
$user = getenv('DB_USER') ?: 'FinalSemester';
$pass = getenv('DB_PASS') ?: 'Chor-Chor@123';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Support SSL options for cloud databases
if (getenv('DB_SSL_CA')) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA');
}

return new PDO($dsn, $user, $pass, $options);
