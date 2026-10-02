<?php
/**
 * AID Database Wrapper
 * Singleton for database connection and secure queries.
 */

class AID_DB {
    private static $instance = null;
    private $connection;

    private function __construct() {
        require_once AID_ROOT . '/config/database.php';
        
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME
            );

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = [], $types = "") {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }

        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat("s", count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function fetchAll($sql, $params = [], $types = "") {
        $result = $this->query($sql, $params, $types);
        return $result->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne($sql, $params = [], $types = "") {
        $result = $this->fetchAll($sql, $params, $types);
        return $result[0] ?? null;
    }

    public function execute($sql, $params = [], $types = "") {
        $stmt = $this->query($sql, $params, $types);
        return $stmt->affected_rows;
    }

    public function getConnection() {
        return $this->connection;
    }
}
