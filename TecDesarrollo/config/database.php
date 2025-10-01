<?php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'mesa_ayuda';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}
