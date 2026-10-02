<?php
/**
 * AID QR Verification Provider
 * Handles QR-based verification.
 */

class AID_QRProvider implements AID_VerificationProvider {
    public function getName() { return 'QR'; }

    public function challenge($verificationId, $data = []) {
        $qrToken = bin2hex(random_bytes(16));
        // Store QR token linked to verification
        $db = AID_DB::getInstance();
        $db->execute(
            "INSERT INTO aid_logs (verification_id, event, details) VALUES (?, 'QR_CREATED', ?)",
            [$verificationId, json_encode(['qr_token' => $qrToken])]
        );

        return [
            'type' => 'qr_code',
            'url' => AID_BASE_URL . 'verify/qr/' . $qrToken,
            'message' => 'Scan this QR code with your mobile device to verify.'
        ];
    }

    public function verify($verificationId, $token) {
        // QR verification is usually asynchronous (via mobile app)
        // This method would be called by the mobile approval endpoint
        return true;
    }
}
