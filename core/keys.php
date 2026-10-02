<?php
/**
 * AID API Key Manager
 * Handles creation, rotation and validation of API keys.
 */

require_once AID_ROOT . '/core/database.php';
require_once AID_ROOT . '/core/security.php';

class AID_KeyManager {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function generateKeysForApp($appId, $type = 'test') {
        $apiKey = AID_Security::generateAPIKey("aid_{$type}_");
        $secret = bin2hex(random_bytes(32));

        $this->db->execute(
            "INSERT INTO aid_api_keys (app_id, api_key, secret, type) VALUES (?, ?, ?, ?)",
            [$appId, $apiKey, $secret, $type]
        );

        return [
            'api_key' => $apiKey,
            'secret' => $secret
        ];
    }

    public function validateKey($apiKey) {
        $keyData = $this->db->fetchOne(
            "SELECT * FROM aid_api_keys WHERE api_key = ?", 
            [$apiKey]
        );

        if (!$keyData) return null;

        // Update last used
        $this->db->execute(
            "UPDATE aid_api_keys SET last_used = NOW() WHERE id = ?", 
            [$keyData['id']]
        );

        return $keyData;
    }

    public function revokeKey($keyId) {
        return $this->db->execute("DELETE FROM aid_api_keys WHERE id = ?", [$keyId]);
    }
}
