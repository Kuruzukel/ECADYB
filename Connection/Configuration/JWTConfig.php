<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define('JWT_SECRET_KEY', getenv('JWT_SECRET') ?: 'your-secret-key-change-this-in-production-2024');
define('JWT_ALGORITHM', 'HS256');
define('JWT_SESSION_DURATION', 3600);

function generateSessionToken($studentId, $role = 'student', $additionalData = [])
{
    $issuedAt = time();
    $expire = $issuedAt + JWT_SESSION_DURATION;

    $payload = [
        'iss' => 'ECADYB',
        'iat' => $issuedAt,
        'exp' => $expire,
        'nbf' => $issuedAt,
        'jti' => bin2hex(random_bytes(16)),
        'data' => array_merge([
            'student_id' => $studentId,
            'role' => $role,
            'login_time' => date('Y-m-d H:i:s', $issuedAt),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], $additionalData)
    ];

    return JWT::encode($payload, JWT_SECRET_KEY, JWT_ALGORITHM);
}

/**
 * Verify and decode JWT token
 */
function verifySessionToken($token)
{
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGORITHM));
        return (array)$decoded->data;
    } catch (Exception $e) {
        error_log("JWT Verification failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Store active session in MongoDB
 */
function storeActiveSession($client, $sessionData)
{
    try {
        $db = $client->ECADYB;
        $sessionsCollection = $db->active_sessions;

        // Remove old session for this user
        $sessionsCollection->deleteMany([
            'student_id' => $sessionData['student_id']
        ]);

        // Insert new session
        $sessionData['last_activity'] = new MongoDB\BSON\UTCDateTime();
        $sessionData['created_at'] = new MongoDB\BSON\UTCDateTime();

        $sessionsCollection->insertOne($sessionData);

        return true;
    } catch (Exception $e) {
        error_log("Error storing active session: " . $e->getMessage());
        return false;
    }
}

/**
 * Update session activity timestamp
 */
function updateSessionActivity($client, $studentId)
{
    try {
        $db = $client->ECADYB;
        $sessionsCollection = $db->active_sessions;

        $sessionsCollection->updateOne(
            ['student_id' => $studentId],
            ['$set' => ['last_activity' => new MongoDB\BSON\UTCDateTime()]]
        );

        return true;
    } catch (Exception $e) {
        error_log("Error updating session activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove active session
 */
function removeActiveSession($client, $studentId)
{
    try {
        $db = $client->ECADYB;
        $sessionsCollection = $db->active_sessions;

        $sessionsCollection->deleteMany([
            'student_id' => $studentId
        ]);

        return true;
    } catch (Exception $e) {
        error_log("Error removing active session: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all active sessions (for admin dashboard)
 */
function getActiveSessions($client)
{
    try {
        $db = $client->ECADYB;
        $sessionsCollection = $db->active_sessions;

        // Remove sessions inactive for more than 1 hour
        $oneHourAgo = new MongoDB\BSON\UTCDateTime((time() - JWT_SESSION_DURATION) * 1000);
        $sessionsCollection->deleteMany([
            'last_activity' => ['$lt' => $oneHourAgo]
        ]);

        // Get remaining active sessions
        $sessions = $sessionsCollection->find([], [
            'sort' => ['last_activity' => -1]
        ]);

        return iterator_to_array($sessions);
    } catch (Exception $e) {
        error_log("Error getting active sessions: " . $e->getMessage());
        return [];
    }
}

/**
 * Clean up expired sessions
 */
function cleanupExpiredSessions($client)
{
    try {
        $db = $client->ECADYB;
        $sessionsCollection = $db->active_sessions;

        $oneHourAgo = new MongoDB\BSON\UTCDateTime((time() - JWT_SESSION_DURATION) * 1000);
        $result = $sessionsCollection->deleteMany([
            'last_activity' => ['$lt' => $oneHourAgo]
        ]);

        return $result->getDeletedCount();
    } catch (Exception $e) {
        error_log("Error cleaning up sessions: " . $e->getMessage());
        return 0;
    }
}
