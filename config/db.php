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

// Support SSL connections (required by cloud providers like TiDB Cloud and Aiven)
$caCertPath = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
if (getenv('DB_SSL') === 'true' || getenv('DB_SSL_CA') || (!empty($host) && strpos($host, 'tidbcloud.com') !== false)) {
    if (file_exists($caCertPath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caCertPath;
    }
    if (getenv('DB_SSL_VERIFY') === 'false') {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

return new PDO($dsn, $user, $pass, $options);
