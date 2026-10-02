<?php
/**
 * AID Shield
 * Post-verification protection system.
 */

require_once AID_ROOT . '/core/database.php';

class AID_Shield {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function validateSession($verificationId, $currentFingerprint) {
        $v = $this->db->fetchOne(
            "SELECT * FROM aid_verifications WHERE verification_id = ?", 
            [$verificationId]
        );

        if (!$v) return ['action' => 'BLOCK', 'reason' => 'Session not found'];
        if ($v['status'] !== 'VERIFIED') return ['action' => 'REVERIFY', 'reason' => 'Not verified'];

        // Detect Replay / Device Change
        if ($v['fingerprint'] !== $currentFingerprint) {
            return ['action' => 'STEP_UP', 'reason' => 'Device inconsistency detected'];
        }

        // Check for expiration
        if (strtotime($v['expires_at']) < time()) {
            return ['action' => 'REVERIFY', 'reason' => 'Session expired'];
        }

        return ['action' => 'ALLOW', 'reason' => 'Session valid'];
    }

    public function reportAbuse($verificationId, $type) {
        $this->db->execute(
            "INSERT INTO aid_logs (verification_id, event, details) VALUES (?, 'ABUSE_REPORTED', ?)",
            [$verificationId, json_encode(['type' => $type])]
        );
    }
}
