<?php
/**
 * AID Installation Processor
 * Handles the logic of setting up the database and config.
 * Designed to work automatically on InfinityFree and similar shared hosts.
 */

require_once '../config/config.php';
require_once '../core/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$db_host = $_POST['db_host'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$db_user = $_POST['db_user'] ?? '';
$db_pass = $_POST['db_pass'] ?? '';
$admin_user = $_POST['admin_user'] ?? '';
$admin_email = $_POST['admin_email'] ?? '';
$admin_pass = $_POST['admin_pass'] ?? '';

if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_user) || empty($admin_pass)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // ==========================================
    // PHASE 1: Create Database if not exists
    // ==========================================
    // First, connect without specifying a database to create it if needed
    $mysqli_server = new mysqli($db_host, $db_user, $db_pass);
    
    if ($mysqli_server->connect_error) {
        throw new Exception("Failed to connect to MySQL server: " . $mysqli_server->connect_error);
    }
    
    // Create database if not exists (compatible with InfinityFree and most hosts)
    $create_db = $mysqli_server->query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci");
    if (!$create_db) {
        throw new Exception("Failed to create database: " . $mysqli_server->error);
    }
    
    // Select the database
    if (!$mysqli_server->select_db($db_name)) {
        throw new Exception("Failed to select database: " . $mysqli_server->error);
    }

    // ==========================================
    // PHASE 2: Run Schema and Create Tables
    // ==========================================
    $mysqli = $mysqli_server; // Use the same connection now that DB is selected
    
    // Run Schema
    $schema = file_get_contents('schema.sql.php');
    // Handle both string schema and array of queries
    if (is_string($schema)) {
        $queries = explode(';', $schema);
    } elseif (is_array($schema)) {
        $queries = $schema;
    } else {
        throw new Exception("Invalid schema format");
    }
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$mysqli->query($query)) {
                // Some tables might already exist on re-installation - that's OK
                if (strpos($mysqli->error, 'already exists') === false && strpos($mysqli->error, 'Duplicate') === false) {
                    throw new Exception("SQL Error in: " . substr($query, 0, 50) . "...: " . $mysqli->error);
                }
            }
        }
    }

    // ==========================================
    // PHASE 3: Create Admin User
    // ==========================================
    $hashed_pass = AID_Security::hashPassword($admin_pass);
    $stmt = $mysqli->prepare("INSERT INTO aid_users (username, password, email) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password=VALUES(password), email=VALUES(email)");
    $stmt->bind_param("sss", $admin_user, $hashed_pass, $admin_email);
    if (!$stmt->execute()) {
        throw new Exception("Failed to create admin user: " . $stmt->error);
    }

    // ==========================================
    // PHASE 4: Save Configuration
    // ==========================================
    $config_content = "<?php\n";
    $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
    $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
    $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
    $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
    $config_content .= "define('AID_BASE_URL', '" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/');\n";
    $config_content .= "define('AID_PLATFORM_NAME', 'AID Platform');\n";

    // Ensure config directory exists and is writable
    $config_dir = '../config';
    if (!is_dir($config_dir)) {
        mkdir($config_dir, 0755, true);
    }
    
    if (file_put_contents($config_dir . '/database.php', $config_content) === false) {
        throw new Exception("Failed to write config file. Check permissions.");
    }

    // Also create a .env file for convenience
    $env_content = "# AID Platform Configuration\n";
    $env_content .= "DB_HOST=$db_host\n";
    $env_content .= "DB_NAME=$db_name\n";
    $env_content .= "DB_USER=$db_user\n";
    $env_content .= "DB_PASS=$db_pass\n";
    $env_content .= "AID_BASE_URL=" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/\n";
    $env_content .= "AID_PLATFORM_NAME=AID Platform\n";
    
    $env_path = AID_ROOT . '/.env';
    if (file_put_contents($env_path, $env_content) === false) {
        // Not critical, just a convenience file
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("AID Install Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Installation failed. ' . $e->getMessage()]);
}
