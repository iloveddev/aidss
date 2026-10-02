<?php
/**
 * AID Verification Provider Interface
 */

interface AID_VerificationProvider {
    public function challenge($verificationId, $data = []);
    public function verify($verificationId, $token);
    public function getName();
}

/**
 * AID Native Provider
 * Simple interactive challenge.
 */
class AID_NativeProvider implements AID_VerificationProvider {
    public function getName() { return 'AID_NATIVE'; }

    public function challenge($verificationId, $data = []) {
        // In a real scenario, this would generate a puzzle or a simple "Click to verify"
        return [
            'type' => 'click_verify',
            'message' => 'Please click the button to verify you are human.'
        ];
    }

    public function verify($verificationId, $token) {
        // Simple token check for MVP
        return $token === 'verified_native';
    }
}

/**
 * Cloudflare Turnstile Provider (Conceptual)
 */
class AID_TurnstileProvider implements AID_VerificationProvider {
    public function getName() { return 'CLOUDFLARE'; }

    public function challenge($verificationId, $data = []) {
        return [
            'type' => 'external_widget',
            'provider' => 'cloudflare',
            'sitekey' => 'your_sitekey_here'
        ];
    }

    public function verify($verificationId, $token) {
        // Here we would call Cloudflare API
        return true; 
    }
}
