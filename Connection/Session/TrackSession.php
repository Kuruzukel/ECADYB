<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/JWTConfig.php';

use MongoDB\Client;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    $client = new Client($mongoUrl);

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'ping':
            // Update last activity for current user (student or admin)
            if (isset($_SESSION['student_id'])) {
                updateSessionActivity($client, $_SESSION['student_id']);
                error_log("✓ Student session ping: " . $_SESSION['student_id']);
                echo json_encode(['success' => true, 'message' => 'Session updated']);
            } elseif (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                // Handle admin session
                $adminId = 'admin_' . $_SESSION['username'];
                updateSessionActivity($client, $adminId);
                error_log("✓ Admin session ping: " . $adminId);
                echo json_encode(['success' => true, 'message' => 'Admin session updated']);
            } else {
                error_log("✗ Session ping failed - No active session. student_id: " . (isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'not set') . ", username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'not set') . ", role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
                echo json_encode(['success' => false, 'message' => 'No active session']);
            }
            break;

        case 'logout':
            // Remove session when user logs out (student or admin)
            if (isset($_SESSION['student_id'])) {
                removeActiveSession($client, $_SESSION['student_id']);
                echo json_encode(['success' => true, 'message' => 'Session removed']);
            } elseif (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                // Handle admin session
                $adminId = 'admin_' . $_SESSION['username'];
                removeActiveSession($client, $adminId);
                echo json_encode(['success' => true, 'message' => 'Admin session removed']);
            }
            break;

        case 'get_active':
            // Get all active sessions (admin only)
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $sessions = getActiveSessions($client);
                $formattedSessions = [];

                foreach ($sessions as $session) {
                    $formattedSessions[] = [
                        'student_id' => $session['student_id'] ?? 'N/A',
                        'name' => $session['name'] ?? 'N/A',
                        'department' => $session['department'] ?? 'N/A',
                        'login_time' => $session['login_time'] ?? 'N/A',
                        'last_activity' => isset($session['last_activity']) ?
                            date('Y-m-d H:i:s', $session['last_activity']->toDateTime()->getTimestamp()) : 'N/A',
                        'ip_address' => $session['ip_address'] ?? 'N/A',
                        'user_agent' => $session['user_agent'] ?? 'N/A'
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'count' => count($formattedSessions),
                    'sessions' => $formattedSessions
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("TrackSession error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
