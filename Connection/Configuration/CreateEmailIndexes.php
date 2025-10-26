<?php


try {
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/MongoConnect.php';
    $database = $GLOBALS['database'];
    $mongoClient = $GLOBALS['mongoClient'];

    echo "Creating email indexes...\n";

    $adminDB = $mongoClient->admin;
    $adminCollection = $adminDB->accounts;

    try {
        $adminCollection->createIndex(['email' => 1], ['unique' => false, 'sparse' => true]);
        echo "✓ Created index on admin.accounts.email\n";
    } catch (Exception $e) {
        echo "✗ Admin index error: " . $e->getMessage() . "\n";
    }

    $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];

    foreach ($departmentCollections as $collectionName) {
        try {
            $collection = $database->selectCollection($collectionName);
            $collection->createIndex(['email' => 1], ['unique' => false, 'sparse' => true]);
            echo "✓ Created index on ECADYB.$collectionName.email\n";
        } catch (Exception $e) {
            echo "✗ $collectionName index error: " . $e->getMessage() . "\n";
        }
    }

    echo "\nIndexes created successfully!\n";
    echo "Email lookups should now be much faster.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
