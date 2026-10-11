<?php
/**
 * AutoCare Garage: Database Configuration
 * Web Development II - CAT 1
 * 
 * Standard XAMPP connection settings (host: 127.0.0.1, user: root, password: '')
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'autocare_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a PDO database connection instance.
 * If connection fails, returns null and logs/stores friendly message.
 *
 * @param string|null &$errorMessage Optional reference to receive friendly error string
 * @return PDO|null
 */
function getDatabaseConnection(&$errorMessage = null) {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Safe, academic-quality error message without exposing server secrets or raw stack traces
        $errorMessage = "Unable to connect to the AutoCare database. Please ensure Apache and MySQL are running in XAMPP and that 'database.sql' has been imported into MariaDB.";
        return null;
    }
}
