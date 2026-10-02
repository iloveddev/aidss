<?php
/**
 * AID Admin Controller
 * Handles dashboard data and application management.
 */

require_once AID_ROOT . '/config/config.php';
require_once AID_ROOT . '/core/database.php';
require_once AID_ROOT . '/core/security.php';
require_once AID_ROOT . '/core/session.php';

class AID_Admin {
    private $db;

    public function __construct() {
        $this->db = AID_DB::getInstance();
        AID_Session::start();
        $this->checkAuth();
    }

    private function checkAuth() {
        if (!AID_Session::get('admin_id')) {
            header('Location: login.php');
            exit;
        }
    }

    public function getOverviewStats() {
        return [
            'verifications' => $this->db->fetchOne("SELECT COUNT(*) as total FROM aid_verifications")['total'],
            'success_rate' => $this->calculateSuccessRate(),
            'active_sessions' => $this->db->fetchOne("SELECT COUNT(*) as total FROM aid_verifications WHERE status = 'PENDING'")['total'],
            'high_risk' => $this->db->fetchOne("SELECT COUNT(*) as total FROM aid_verifications WHERE risk_score > 70")['total'],
        ];
    }

    private function calculateSuccessRate() {
        $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM aid_verifications")['total'];
        if (!$total) return '0%';
        $success = $this->db->fetchOne("SELECT COUNT(*) as total FROM aid_verifications WHERE status = 'VERIFIED'")['total'];
        return round(($success / $total) * 100, 1) . '%';
    }

    public function listApplications() {
        return $this->db->fetchAll("SELECT * FROM aid_applications ORDER BY created_at DESC");
    }

    public function createApplication($name, $domain) {
        $appId = bin2hex(random_bytes(8));
        $this->db->execute(
            "INSERT INTO aid_applications (app_id, name, allowed_domains) VALUES (?, ?, ?)",
            [$appId, $name, $domain]
        );
        return $appId;
    }
}
