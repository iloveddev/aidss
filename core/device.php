<?php
/**
 * AID Device Trust Manager
 * Handles device fingerprinting and trust levels.
 */

class AID_DeviceTrust {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function getDeviceId($fingerprint) {
        // In a real scenario, we would hash the fingerprint
        return hash('sha256', $fingerprint);
    }

    public function trustDevice($appId, $deviceId, $duration = 2592000) { // 30 days
        $expiresAt = date('Y-m-d H:i:s', time() + $duration);
        
        $this->db->execute(
            "INSERT INTO aid_device_trust (device_id, app_id, expires_at) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE expires_at = ?",
            [$deviceId, $appId, $expiresAt, $expiresAt]
        );
    }

    public function isTrusted($appId, $deviceId) {
        $trust = $this->db->fetchOne(
            "SELECT * FROM aid_device_trust WHERE app_id = ? AND device_id = ?",
            [$appId, $deviceId]
        );

        if (!$trust) return false;
        if (strtotime($trust['expires_at']) < time()) return false;
        
        return true;
    }

    public function revokeTrust($appId, $deviceId) {
        $this->db->execute(
            "DELETE FROM aid_device_trust WHERE app_id = ? AND device_id = ?",
            [$appId, $deviceId]
        );
    }
}
