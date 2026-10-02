<?php
/**
 * AID Policy Engine
 * Decides which verification method to use based on risk and rules.
 */

class AID_PolicyEngine {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
    }

    public function decide($appId, $riskScore) {
        // 1. Fetch application policy
        $policy = $this->db->fetchOne(
            "SELECT * FROM aid_policies WHERE app_id = ? AND is_default = 1", 
            [$appId]
        );

        if (!$policy) {
            return $this->getDefaultDecision($riskScore);
        }

        $rules = json_decode($policy['rules'], true);
        return $this->evaluateRules($rules, $riskScore);
    }

    private function evaluateRules($rules, $riskScore) {
        // Example rules structure:
        // { "low": "AID_NATIVE", "medium": "CLOUDFLARE", "high": "QR", "very_high": "PASSKEY" }
        
        if ($riskScore < 20) return $rules['low'] ?? 'AID_NATIVE';
        if ($riskScore < 50) return $rules['medium'] ?? 'AID_NATIVE';
        if ($riskScore < 80) return $rules['high'] ?? 'AID_NATIVE';
        return $rules['very_high'] ?? 'AID_NATIVE';
    }

    private function getDefaultDecision($riskScore) {
        if ($riskScore < 50) return 'AID_NATIVE';
        return 'INTERACTIVE_CHALLENGE';
    }
}
