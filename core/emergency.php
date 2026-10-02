<?php
/**
 * AID Emergency Mode
 * Global security override.
 */

class AID_EmergencyMode {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function isActive() {
        // Check a global setting in config or DB
        return false; 
    }

    public function forceVerification($riskScore) {
        if ($this->isActive()) {
            return 'INTERACTIVE_CHALLENGE'; // Force high friction
        }
        return null;
    }
}
