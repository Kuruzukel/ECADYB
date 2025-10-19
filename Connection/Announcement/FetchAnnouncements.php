<?php
header('Content-Type: application/json');
require __DIR__ . '/../Configuration/MongoConnect.php';

try {
    $announcements = $calendarCollection->find(
        ['status' => 'active'],
        [
            'sort' => ['created_at' => -1],
            'limit' => 10 // Limit to 10 most recent announcements
        ]
    );

    $announcementData = [];

    foreach ($announcements as $announcement) {
        $announcementData[] = [
            'id' => (string)$announcement['_id'],
            'title' => $announcement['title'] ?? 'Announcement',
            'message' => $announcement['message'] ?? '',
            'date' => $announcement['date'] ?? '',
            'time' => $announcement['time'] ?? '',
            'type' => $announcement['type'] ?? 'announcement',
            'created_at' => $announcement['created_at'] ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $announcementData,
        'count' => count($announcementData)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ]);
}
