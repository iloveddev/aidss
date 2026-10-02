<?php
/**
 * AID Verification Engine
 * Core logic for managing verification sessions.
 */

require_once AID_ROOT . '/core/database.php';
require_once AID_ROOT . '/core/security.php';

class AID_Verification {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function createSession($appId, $action = 'default', $deviceId = null) {
        $verificationId = bin2hex(random_bytes(16));
        $sessionId = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->db->execute(
            "INSERT INTO aid_verifications (verification_id, app_id, session_id, device_id, status, action, expires_at) VALUES (?, ?, ?, ?, 'CREATED', ?, ?)",
            [$verificationId, $appId, $sessionId, $deviceId, $action, $expiresAt]
        );

        return [
            'verification_id' => $verificationId,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt
        ];
    }

    public function updateStatus($verificationId, $status, $provider = null, $method = null) {
        $sql = "UPDATE aid_verifications SET status = ?";
        $params = [$status];
        $types = "s";

        if ($provider) {
            $sql .= ", provider = ?";
            $params[] = $provider;
            $types .= "s";
        }
        if ($method) {
            $sql .= ", method = ?";
            $params[] = $method;
            $types .= "s";
        }

        $sql .= " WHERE verification_id = ?";
        $params[] = $verificationId;
        $types .= "s";

        $this->db->execute($sql, $params, $types);
        
        if ($status === 'VERIFIED') {
            $this->db->execute("UPDATE aid_verifications SET completed_at = NOW() WHERE verification_id = ?", [$verificationId]);
        }
    }

    public function getVerification($verificationId) {
        return $this->db->fetchOne(
            "SELECT * FROM aid_verifications WHERE verification_id = ?", 
            [$verificationId]
        );
    }

    public function validate($verificationId) {
        $v = $this->getVerification($verificationId);
        if (!$v) return false;
        
        if ($v['status'] !== 'VERIFIED') return false;
        if (strtotime($v['expires_at']) < time()) return false;
        
        return true;
    }
}
