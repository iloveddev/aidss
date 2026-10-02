<?php
/**
 * AID Universal Local Cache
 * Handles verification caching to avoid redundant challenges.
 */

class AID_VerificationCache {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function setCache($appId, $deviceId, $action, $duration = 3600) {
        $expiresAt = date('Y-m-d H:i:s', time() + $duration);
        
        // We use the logs or a specific cache table. 
        // For MVP, let's use a simple entry in aid_verifications with a specific status
        // or a dedicated table. Let's assume we add a cache table or use device_trust.
        // For now, we'll simulate it by updating a 'verified' status in a cache-like way.
    }

    public function isValid($appId, $deviceId, $action) {
        // Check if there's a recent successful verification for this device/app/action
        $v = $this->db->fetchOne(
            "SELECT * FROM aid_verifications 
             WHERE app_id = ? AND device_id = ? AND action = ? AND status = 'VERIFIED' 
             ORDER BY completed_at DESC LIMIT 1",
            [$appId, $deviceId, $action]
        );

        if (!$v) return false;
        
        // Check if it's within the cache window (e.g., 1 hour)
        if (strtotime($v['completed_at']) < time() - 3600) return false;
        
        return true;
    }
}
