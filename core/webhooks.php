<?php
/**
 * AID Webhook Manager
 * Handles outgoing notifications for verification events.
 */

class AID_WebhookManager {
    public function dispatch($appId, $event, $data) {
        // 1. Get app webhook URL
        // (In real app, fetch from aid_applications table)
        $webhookUrl = 'https://client-site.com/aid/webhook'; 
        
        $payload = json_encode([
            'event' => $event,
            'timestamp' => time(),
            'data' => $data,
            'signature' => $this->signPayload($data)
        ]);

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    private function signPayload($data) {
        // Use app secret to sign
        return hash_hmac('sha256', json_encode($data), 'app_secret_key');
    }
}
