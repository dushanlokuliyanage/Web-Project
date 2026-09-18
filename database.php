<?php

$config_file = __DIR__ . "/.env";

if (!is_readable($config_file)) {
    die("Database configuration file is missing.");
}

$config = parse_ini_file($config_file);

if ($config === false) {
    die("Database configuration file is invalid.");
}

$host = $config["DB_HOST"] ?? "localhost";
$username = $config["DB_USERNAME"] ?? "";
$password = $config["DB_PASSWORD"] ?? "";
$database = $config["DB_DATABASE"] ?? "";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
