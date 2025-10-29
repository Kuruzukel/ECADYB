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

try {
    $client = new Client($mongoUrl);

    echo "=================================================\n";
    echo "PASSWORD HASHING SCRIPT\n";
    echo "=================================================\n\n";

    $totalUpdated = 0;
    $totalSkipped = 0;
    $totalErrors = 0;

    // ========================================
    // Hash Admin Passwords
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

            // Check if password is already hashed (bcrypt hashes start with $2y$)
            if (strpos($currentPassword, '$2y$') === 0) {
                echo "  ⊗ Admin '{$admin['username']}' - Password already hashed (skipped)\n";
                $totalSkipped++;
                continue;
            }

            // Hash the password
            $hashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);

            try {
                $result = $adminCollection->updateOne(
                    ['_id' => $admin['_id']],
                    ['$set' => ['password' => $hashedPassword]]
                );

                if ($result->getModifiedCount() > 0) {
                    echo "  ✓ Admin '{$admin['username']}' - Password hashed successfully\n";
                    $adminUpdated++;
                    $totalUpdated++;
                } else {
                    echo "  ⚠ Admin '{$admin['username']}' - No changes made\n";
                    $totalSkipped++;
                }
            } catch (Exception $e) {
                echo "  ✗ Admin '{$admin['username']}' - Error: {$e->getMessage()}\n";
                $totalErrors++;
            }
        } else {
            echo "  ⚠ Admin '{$admin['username']}' - No password field found\n";
            $totalSkipped++;
        }
    }

    echo "\n  Summary: {$adminUpdated} out of {$adminCount} admin passwords hashed\n\n";

    // ========================================
    // Hash Student Passwords
    // ========================================
    echo "2. Processing STUDENT collections...\n";
    echo "-------------------------------------------------\n";

    $ecadybDB = $client->ECADYB;

    $departmentCollections = [
        'bsme' => 'BS Marine Engineering',
        'bsmt' => 'BS Marine Transportation',
        'bscje' => 'BS Criminal Justice Education',
        'bstm' => 'BS Tourism Management',
        'btvted' => 'BS Technical-Vocational Teacher Education',
        'beced' => 'BS Early Childhood Education',
        'bsn' => 'BS Nursing',
        'bsis' => 'BS Information System',
        'bsma' => 'BS Management Accounting',
        'bse' => 'BS Entrepreneurship'
    ];

    foreach ($departmentCollections as $collectionName => $departmentName) {
        echo "\n  Processing: {$departmentName} ({$collectionName})\n";

        $collection = $ecadybDB->{$collectionName};
        $students = $collection->find([]);

        $deptCount = 0;
        $deptUpdated = 0;

        foreach ($students as $student) {
            $deptCount++;

            if (isset($student['password']) && !empty($student['password'])) {
                $currentPassword = $student['password'];

                // Check if password is already hashed
                if (strpos($currentPassword, '$2y$') === 0) {
                    $totalSkipped++;
                    continue; // Skip already hashed passwords (don't show message to reduce clutter)
                }

                // Hash the password
                $hashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);

                try {
                    $result = $collection->updateOne(
                        ['_id' => $student['_id']],
                        ['$set' => ['password' => $hashedPassword]]
                    );

                    if ($result->getModifiedCount() > 0) {
                        $studentName = ($student['first name'] ?? '') . ' ' . ($student['last name'] ?? '');
                        $studentId = $student['student id'] ?? $student['student_id'] ?? 'Unknown';
                        echo "    ✓ Student ID: {$studentId} - {$studentName}\n";
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
            }
        }

        if ($deptUpdated > 0) {
            echo "    → {$deptUpdated} out of {$deptCount} passwords hashed\n";
        } else {
            echo "    → All passwords already hashed or no passwords found\n";
        }
    }

    // ========================================
    // Final Summary
    // ========================================
    echo "\n=================================================\n";
    echo "FINAL SUMMARY\n";
    echo "=================================================\n";
    echo "Total passwords hashed:   {$totalUpdated}\n";
    echo "Total already hashed:     {$totalSkipped}\n";
    echo "Total errors:             {$totalErrors}\n";
    echo "=================================================\n\n";

    if ($totalUpdated > 0) {
        echo "✓ SUCCESS: All passwords have been hashed!\n";
        echo "\nIMPORTANT NEXT STEPS:\n";
        echo "1. Update your login scripts to use password_verify() instead of plain text comparison\n";
        echo "2. Update password change scripts to use password_hash() for new passwords\n";
        echo "3. Test the login functionality thoroughly\n";
        echo "4. Consider backing up your database before making further changes\n";
    } else {
        echo "ℹ INFO: No passwords needed to be hashed (all already hashed or no passwords found)\n";
    }
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
