<?php

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

// Get MongoDB URL
$mongoUrl = getenv('MONGO_URL') ?: $_ENV['MONGO_URL'] ?? getenv('MONGODB_URI') ?? $_ENV['MONGODB_URI'] ?? null;
if (!$mongoUrl) {
    die("ERROR: MongoDB URL not configured.\n");
}

try {
    $client = new Client($mongoUrl);

    echo "=================================================\n";
    echo "UPDATE COMPLETION DATE - TESTING SCRIPT\n";
    echo "=================================================\n\n";

    $coversCollection = $client->ECADYB->Yearbook_Covers;

    // Set specific completion date: October 27, 2025 at 11:26 PM
    $completionDate = new DateTime('2025-10-27 23:26:00');
    $completionTimestamp = new UTCDateTime($completionDate->getTimestamp() * 1000);

    echo "Current time: " . (new DateTime())->format('Y-m-d H:i:s') . "\n";
    echo "New completion date: " . $completionDate->format('Y-m-d H:i:s') . "\n\n";

    // Update all documents with batch_year "Batch Year 2024-2025" and template 1
    $result = $coversCollection->updateMany(
        [
            'batch_year' => 'Batch Year 2024-2025',
            'template' => 1
        ],
        [
            '$set' => ['completion_date' => $completionTimestamp]
        ]
    );

    echo "Documents matched: " . $result->getMatchedCount() . "\n";
    echo "Documents modified: " . $result->getModifiedCount() . "\n\n";

    if ($result->getModifiedCount() > 0) {
        echo "✓ SUCCESS: Completion date updated!\n";
        echo "  Batch Year: Batch Year 2024-2025\n";
        echo "  Template: 1\n";
        echo "  New completion date: " . $completionDate->format('Y-m-d H:i:s T') . "\n";
        echo "  (ISO format: " . $completionDate->format('c') . ")\n\n";
        echo "⏰ The yearbook will lock on October 27, 2025 at 11:26 PM!\n";
        echo "   Students can access yearbooks until then.\n";
    } else {
        echo "⚠ No documents were modified.\n";
        echo "  Make sure the batch_year and template match your data.\n";
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
