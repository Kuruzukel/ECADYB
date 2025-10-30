<?php

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

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

$upper = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
$lower = 'abcdefghijkmnopqrstuvwxyz';
$digits = '123456789';
$special = '!@#_$';

function generateRandomPassword($length = 8)
{
    global $upper, $lower, $digits, $special;

    $characters = $upper . $lower . $digits . $special;
    $password = '';
    $charactersLength = strlen($characters);

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, $charactersLength - 1)];
    }

    return $password;
}

try {
    $client = new Client($mongoUrl);

    echo "=================================================\n";
    echo "PASSWORD UNHASHING SCRIPT\n";
    echo "This will reset all hashed passwords to plain text\n";
    echo "=================================================\n\n";

    $totalUpdated = 0;
    $totalSkipped = 0;
    $totalErrors = 0;

    // ========================================
    // Reset Admin Passwords
    // ========================================
    echo "1. Processing ADMIN collection...\n";
    echo "-------------------------------------------------\n";

    $adminDB = $client->admin;
    $adminCollection = $adminDB->accounts;

    $admins = $adminCollection->find([]);
    $adminCount = 0;
    $adminUpdated = 0;

    foreach ($admins as $admin) {
        $adminCount++;

        if (isset($admin['password']) && !empty($admin['password'])) {
            $currentPassword = $admin['password'];

            // Check if password is hashed (bcrypt hashes start with $2y$)
            if (strpos($currentPassword, '$2y$') === 0) {
                // Generate a new plain text password
                $plainPassword = generateRandomPassword(8);

                try {
                    $result = $adminCollection->updateOne(
                        ['_id' => $admin['_id']],
                        ['$set' => ['password' => $plainPassword]]
                    );

                    if ($result->getModifiedCount() > 0) {
                        echo "  ✓ Admin '{$admin['username']}' - New password: {$plainPassword}\n";
                        $adminUpdated++;
                        $totalUpdated++;
                    } else {
                        $totalSkipped++;
                    }
                } catch (Exception $e) {
                    echo "  ✗ Admin '{$admin['username']}' - Error: {$e->getMessage()}\n";
                    $totalErrors++;
                }
            } else {
                echo "  ⊗ Admin '{$admin['username']}' - Password already plain text (skipped)\n";
                $totalSkipped++;
            }
        }
    }

    echo "\nAdmin Summary: $adminUpdated updated, " . ($adminCount - $adminUpdated) . " skipped\n\n";

    // ========================================
    // Reset Student Passwords
    // ========================================
    echo "2. Processing STUDENT collections...\n";
    echo "-------------------------------------------------\n";

    $ecadybDB = $client->ECADYB;

    $studentCollections = [
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

    foreach ($studentCollections as $collectionName => $departmentName) {
        echo "\n  Processing: {$departmentName} ({$collectionName})\n";

        $collection = $ecadybDB->{$collectionName};
        $students = $collection->find([]);

        $deptCount = 0;
        $deptUpdated = 0;

        foreach ($students as $student) {
            $deptCount++;

            if (isset($student['password']) && !empty($student['password'])) {
                $currentPassword = $student['password'];

                // Check if password is hashed
                if (strpos($currentPassword, '$2y$') === 0) {
                    // Generate a new plain text password
                    $plainPassword = generateRandomPassword(8);

                    try {
                        $result = $collection->updateOne(
                            ['_id' => $student['_id']],
                            ['$set' => ['password' => $plainPassword]]
                        );

                        if ($result->getModifiedCount() > 0) {
                            $studentName = ($student['first name'] ?? '') . ' ' . ($student['last name'] ?? '');
                            $studentId = $student['student id'] ?? $student['student_id'] ?? 'Unknown';
                            echo "    ✓ Student ID: {$studentId} - {$studentName} - Password: {$plainPassword}\n";
                            $deptUpdated++;
                            $totalUpdated++;
                        } else {
                            $totalSkipped++;
                        }
                    } catch (Exception $e) {
                        $studentId = $student['student id'] ?? $student['student_id'] ?? 'Unknown';
                        echo "    ✗ Student ID: {$studentId} - Error: {$e->getMessage()}\n";
                        $totalErrors++;
                    }
                } else {
                    // Password is already plain text, skip
                    $totalSkipped++;
                }
            }
        }

        if ($deptUpdated > 0) {
            echo "  Department Summary: $deptUpdated updated out of $deptCount students\n";
        } else {
            echo "  Department Summary: No hashed passwords found (all already plain text)\n";
        }
    }

    // ========================================
    // Final Summary
    // ========================================
    echo "\n=================================================\n";
    echo "FINAL SUMMARY\n";
    echo "=================================================\n";
    echo "Total passwords reset:   $totalUpdated\n";
    echo "Total already plain:     $totalSkipped\n";
    echo "Total errors:            $totalErrors\n";
    echo "=================================================\n\n";

    if ($totalUpdated > 0) {
        echo "✅ SUCCESS: All hashed passwords have been reset to plain text.\n";
        echo "⚠️  IMPORTANT: Save the new passwords shown above!\n";
        echo "   They are logged in the PHP error log as well.\n\n";
    } else {
        echo "ℹ️  INFO: No hashed passwords found. All passwords are already plain text.\n\n";
    }
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
