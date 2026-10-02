<?php
/**
 * AID Risk Engine
 * Analyzes request signals to determine risk score.
 */

class AID_RiskEngine {
    public function analyze($requestData) {
        $score = 0;
        $signals = [];

        // 1. IP Analysis (Conceptual)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ipRisk = $this->analyzeIP($ip);
        $score += $ipRisk['score'];
        $signals['ip'] = $ipRisk;

        // 2. User Agent / Device Analysis
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $deviceRisk = $this->analyzeDevice($ua);
        $score += $deviceRisk['score'];
        $signals['device'] = $deviceRisk;

        // 3. Behavioral signals (Conceptual)
        // e.g., rapid requests, unusual headers
        
        return [
            'score' => min(100, $score),
            'signals' => $signals,
            'level' => $this->getRiskLevel(min(100, $score))
        ];
    }

    private function analyzeIP($ip) {
        // In a real scenario, this would check against VPN/Proxy lists or GeoIP
        // For MVP, we simulate some logic
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            return ['score' => 10, 'reason' => 'Private IP'];
        }
        return ['score' => 0, 'reason' => 'Clean'];
    }

    private function analyzeDevice($ua) {
        if (empty($ua)) {
            return ['score' => 50, 'reason' => 'Missing User Agent'];
        }
        if (stripos($ua, 'Headless') !== false || stripos($ua, 'Bot') !== false) {
            return ['score' => 80, 'reason' => 'Bot detected'];
        }
        return ['score' => 0, 'reason' => 'Normal browser'];
    }

    private function getRiskLevel($score) {
        if ($score < 20) return 'LOW';
        if ($score < 50) return 'MEDIUM';
        if ($score < 80) return 'HIGH';
        return 'VERY_HIGH';
    }
}
