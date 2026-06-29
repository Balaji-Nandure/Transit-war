<?php
// Database connection for NSP
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'transactiwar';
//$user = getenv('DB_USER') ?: 'nspuser';
//$pass = getenv('DB_PASS') ?: 'nsppass';
$user = getenv('DB_USER') ?: 'FinalSemester';
$pass = getenv('DB_PASS') ?: 'Chor-Chor@123';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
return new PDO($dsn, $user, $pass, $options);
