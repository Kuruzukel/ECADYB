<?php

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

// Load environment variables
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
    echo "SET COMPLETION DATE TO FUTURE - NORMAL OPERATION\n";
    echo "=================================================\n\n";

    $coversCollection = $client->ECADYB->Yearbook_Covers;

    // Set completion date to 7 days from now
    $completionDate = new DateTime('now');
    $completionDate->modify('+7 days');
    $completionTimestamp = new UTCDateTime($completionDate->getTimestamp() * 1000);

    echo "Current time: " . (new DateTime())->format('Y-m-d H:i:s') . "\n";
    echo "Setting completion date to: " . $completionDate->format('Y-m-d H:i:s') . " (7 days from now)\n\n";

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
        echo "✓ SUCCESS: Completion date set to FUTURE!\n";
        echo "  Batch Year: Batch Year 2024-2025\n";
        echo "  Template: 1\n";
        echo "  Completion date: " . $completionDate->format('Y-m-d H:i:s T') . "\n\n";
        echo "✅ The yearbook is NOW ACTIVE for 7 days!\n";
        echo "   Students can access digital yearbooks until " . $completionDate->format('M d, Y g:i A') . "\n";
        echo "   After that date, only PDF downloads will be available.\n";
    } else {
        echo "⚠ No documents were modified.\n";
        echo "  Make sure the batch_year and template match your data.\n";
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
