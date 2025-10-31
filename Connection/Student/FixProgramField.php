<?php

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Fix Program Field</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
.success { color: #4CAF50; padding: 10px; background: #f1f8f4; border-left: 4px solid #4CAF50; margin: 10px 0; }
.error { color: #f44336; padding: 10px; background: #fef1f0; border-left: 4px solid #f44336; margin: 10px 0; }
.info { color: #2196F3; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196F3; margin: 10px 0; }
.warning { color: #ff9800; padding: 10px; background: #fff3e0; border-left: 4px solid #ff9800; margin: 10px 0; }
.summary { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #4CAF50; color: white; }
tr:hover { background: #f5f5f5; }
</style></head><body><div class='container'>";

echo "<h1>🔧 Fix Program Field Migration</h1>";

try {
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    $client = new Client($mongoUrl);

    echo "<div class='success'>✅ Connected to MongoDB successfully</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Failed to connect to MongoDB: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "</div></body></html>";
    exit;
}

$programCollections = [
    "bsme"   => "BS Marine Engineering",
    "bsmt"   => "BS Marine Transportation",
    "bscje"  => "BS Criminal Justice Education",
    "bstm"   => "BS Tourism Management",
    "btvted" => "BS Technical-Vocational Teacher Education",
    "beced"  => "BS Early Childhood Education",
    "bsn"    => "BS Nursing",
    "bsis"   => "BS Information System",
    "bsma"   => "BS Management Accounting",
    "bse"    => "BS Entrepreneurship"
];

$dbName = "ECADYB";
$db = $client->$dbName;

echo "<div class='info'>📊 Scanning collections in database: <strong>$dbName</strong></div>";

$totalFixed = 0;
$totalScanned = 0;
$errors = 0;

echo "<table>";
echo "<thead><tr><th>Collection</th><th>Students Scanned</th><th>Fixed</th><th>Status</th></tr></thead>";
echo "<tbody>";

foreach ($programCollections as $collectionKey => $fullName) {
    try {
        $collection = $db->$collectionKey;

        $totalDocs = $collection->countDocuments();
        $totalScanned += $totalDocs;

        if ($totalDocs == 0) {
            echo "<tr><td><strong>$collectionKey</strong> ($fullName)</td><td>0</td><td>-</td><td><span style='color: #999;'>Empty</span></td></tr>";
            continue;
        }

        // Find all documents where program field doesn't match collection name
        $incorrectPrograms = $collection->find([
            '$or' => [
                ['program' => ['$ne' => $collectionKey]],
                ['program' => ['$exists' => false]]
            ]
        ]);

        $fixedCount = 0;
        $docList = iterator_to_array($incorrectPrograms);

        foreach ($docList as $doc) {
            $oldProgram = $doc['program'] ?? 'NOT SET';
            $studentId = $doc['student id'] ?? $doc['student_id'] ?? 'NO ID';

            // Update the program field to match the collection
            $result = $collection->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['program' => $collectionKey]]
            );

            if ($result->getModifiedCount() > 0) {
                $fixedCount++;
                error_log("Fixed student $studentId in $collectionKey: changed program from '$oldProgram' to '$collectionKey'");
            }
        }

        $totalFixed += $fixedCount;

        if ($fixedCount > 0) {
            echo "<tr><td><strong>$collectionKey</strong> ($fullName)</td><td>$totalDocs</td><td><strong style='color: #4CAF50;'>$fixedCount</strong></td><td><span style='color: #4CAF50;'>✅ Fixed</span></td></tr>";
        } else {
            echo "<tr><td><strong>$collectionKey</strong> ($fullName)</td><td>$totalDocs</td><td>0</td><td><span style='color: #4CAF50;'>✓ OK</span></td></tr>";
        }
    } catch (Exception $e) {
        $errors++;
        echo "<tr><td><strong>$collectionKey</strong></td><td colspan='3'><span style='color: #f44336;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span></td></tr>";
        error_log("Error processing collection $collectionKey: " . $e->getMessage());
    }
}

echo "</tbody></table>";

echo "<div class='summary'>";
echo "<h2>📈 Migration Summary</h2>";
echo "<p><strong>Total Students Scanned:</strong> $totalScanned</p>";
echo "<p><strong>Total Students Fixed:</strong> <span style='color: #4CAF50; font-size: 1.5em;'>$totalFixed</span></p>";
echo "<p><strong>Errors:</strong> " . ($errors > 0 ? "<span style='color: #f44336;'>$errors</span>" : "<span style='color: #4CAF50;'>0</span>") . "</p>";
echo "</div>";

if ($totalFixed > 0) {
    echo "<div class='success'>";
    echo "<h3>✅ Migration Completed Successfully!</h3>";
    echo "<p>All student records now have the correct program field matching their collection.</p>";
    echo "</div>";
} else {
    echo "<div class='info'>";
    echo "<h3>ℹ️ No Changes Needed</h3>";
    echo "<p>All student records already have the correct program field.</p>";
    echo "</div>";
}

echo "<div class='warning'>";
echo "<p><strong>⚠️ Important:</strong> You can safely delete this migration script after running it once.</p>";
echo "<p>File location: <code>Connection/Student/FixProgramField.php</code></p>";
echo "</div>";

echo "</div></body></html>";
