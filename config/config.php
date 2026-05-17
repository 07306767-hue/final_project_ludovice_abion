<?php
/**
 * Database connection configuration.
 *
 * This file creates a shared mysqli connection used by pages
 * under public/pages. It also supports environment overrides
 * using DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT and DB_CHARSET.
 *
 * Sensitive data → ENV: Database credentials loaded from environment variables
 * If sensitive → ENV: Passwords and connection details should never be hardcoded
 */

require_once __DIR__ . '/../src/Helpers/Constants.php'; // Load constants

// Enable mysqli exceptions for better debugging during development.
// Magic numbers → Constants: Using defined constants instead of raw values
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = getenv('DB_HOST') ?: DEFAULT_DB_HOST; // Magic string → constant
$dbUser = getenv('DB_USER') ?: 'root'; // Could be constant but varies by environment
$dbPassword = getenv('DB_PASSWORD') ?: ''; // Sensitive → ENV
$dbName = getenv('DB_NAME') ?: DEFAULT_DB_NAME; // Magic string → constant
$dbPort = getenv('DB_PORT') ?: DEFAULT_DB_PORT; // Magic number → constant
$dbCharset = getenv('DB_CHARSET') ?: DEFAULT_DB_CHARSET; // Magic string → constant

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName, (int) $dbPort);
    $conn->set_charset($dbCharset);
} catch (mysqli_sql_exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

/**
 * Return the shared mysqli connection.
 *
 * @return mysqli
 */
function db_connect(): mysqli
{
    global $conn;
    return $conn;
}

/**
 * Prepare a SQL statement using the shared mysqli connection.
 *
 * @param string $sql
 * @return mysqli_stmt
 */
function db_prepare(string $sql): mysqli_stmt
{
    return db_connect()->prepare($sql);
}

/**
 * Escape a string for use in SQL if needed.
 *
 * @param string $value
 * @return string
 */
function db_escape(string $value): string
{
    return db_connect()->real_escape_string($value);
}
?>