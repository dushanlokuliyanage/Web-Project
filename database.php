<?php

// Railway: read environment variables first.
$host     = getenv("DB_HOST");
$port     = getenv("DB_PORT") ?: 3306;
$username = getenv("DB_USERNAME");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_DATABASE");

// Local: fall back to the .env file if Railway variables are not set.
if ($host === false || $host === "") {
    $config_file = __DIR__ . "/.env";

    if (!is_readable($config_file)) {
        die("Database configuration file is missing.");
    }

    $config = parse_ini_file($config_file);

    if ($config === false) {
        die("Database configuration file is invalid.");
    }

    $host     = $config["DB_HOST"] ?? "localhost";
    $port     = $config["DB_PORT"] ?? 3306;
    $username = $config["DB_USERNAME"] ?? "";
    $password = $config["DB_PASSWORD"] ?? "";
    $database = $config["DB_DATABASE"] ?? "";
}

$conn = new mysqli($host, $username, $password, $database, (int)$port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ... keep your event_registrations CREATE TABLE code below, unchanged

/*
 * This table is used by the dashboard, event details, and registrations pages.
 * Keep this migration here so existing installations receive the missing table
 * without requiring a manual database import.
 */
$registration_table_sql = "
    CREATE TABLE IF NOT EXISTS event_registrations (
        registration_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        event_id INT UNSIGNED NOT NULL,
        registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (registration_id),
        UNIQUE KEY unique_event_registration (user_id, event_id),
        KEY event_registrations_event_id (event_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
";

if (!$conn->query($registration_table_sql)) {
    die("Unable to initialize event registrations: " . $conn->error);
}
