<?php
/**
 * AID Simulator
 * Allows developers to test risk scenarios and policy decisions.
 */

require_once AID_ROOT . '/core/risk.php';
require_once AID_ROOT . '/core/policy.php';

class AID_Simulator {
    public function simulate($params) {
        $riskScore = 0;
        $reasons = [];

        // Simulate signals
        if (($params['vpn'] ?? 'no') === 'yes') {
            $riskScore += 40;
            $reasons[] = 'VPN Detected';
        }
        if (($params['proxy'] ?? 'no') === 'yes') {
            $riskScore += 30;
            $reasons[] = 'Proxy Detected';
        }
        if (($params['country'] ?? 'unknown') === 'restricted') {
            $riskScore += 50;
            $reasons[] = 'Restricted Country';
        }
        if (($params['device'] ?? 'known') === 'new') {
            $riskScore += 15;
            $reasons[] = 'New Device';
        }

        $riskScore = min(100, $riskScore);
        
        // Get decision from policy engine
        $policyEngine = new AID_PolicyEngine();
        $decision = $policyEngine->decide($params['app_id'] ?? 1, $riskScore);

        return [
            'risk_score' => $riskScore,
            'reasons' => $reasons,
            'decision' => $decision,
            'provider' => $this->getProviderName($decision)
        ];
    }

    private function getProviderName($decision) {
        $map = [
            'AID_NATIVE' => 'AID Native',
            'CLOUDFLARE' => 'Cloudflare Turnstile',
            'QR' => 'QR Verification',
            'PASSKEY' => 'Passkey / WebAuthn'
        ];
        return $map[$decision] ?? 'Unknown';
    }
}
