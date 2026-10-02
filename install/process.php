<?php
/**
 * AID Installation Processor
 * Handles the logic of setting up the database and config.
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
    // 1. Test Connection
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // 2. Run Schema
    $schema = require 'schema.sql.php';
    $queries = explode(';', $schema);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$mysqli->query($query)) {
                throw new Exception("SQL Error: " . $mysqli->error);
            }
        }
    }

    // 3. Create Admin User
    $hashed_pass = AID_Security::hashPassword($admin_pass);
    $stmt = $mysqli->prepare("INSERT INTO aid_users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $admin_user, $hashed_pass, $admin_email);
    if (!$stmt->execute()) {
        throw new Exception("Failed to create admin user: " . $stmt->error);
    }

    // 4. Save Configuration
    $config_content = "<?php\n";
    $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
    $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
    $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
    $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
    $config_content .= "define('AID_BASE_URL', '" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/');\n";
    $config_content .= "define('AID_PLATFORM_NAME', 'AID Platform');\n";

    if (file_put_contents('../config/database.php', $config_content) === false) {
        throw new Exception("Failed to write config file. Check permissions.");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
