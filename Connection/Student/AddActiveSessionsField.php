<?php
/**
 * Migration Script: Add active_sessions field to student accounts
 * 
 * This script adds the active_sessions field to all student accounts
 * across all department collections if it doesn't exist.
 * 
 * Usage: Run this file once via browser or CLI
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/EnvLoader.php';

use MongoDB\Client;

try {
    echo "<h2>Adding active_sessions field to student accounts...</h2>";
    
    $client = new Client(getMongoUrl());
    $database = $client->selectDatabase('ECADYB');
    
    $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];
    
    $totalUpdated = 0;
    $totalAccounts = 0;
    
    foreach ($departmentCollections as $collectionName) {
        echo "<h3>Processing: $collectionName</h3>";
        
        $collection = $database->selectCollection($collectionName);
        
        // Count total in this collection
        $collectionTotal = $collection->countDocuments([]);
        $totalAccounts += $collectionTotal;
        
        // Find accounts without active_sessions field
        $accountsWithoutField = $collection->find([
            'active_sessions' => ['$exists' => false]
        ]);
        
        $count = 0;
        foreach ($accountsWithoutField as $account) {
            $result = $collection->updateOne(
                ['_id' => $account['_id']],
                ['$set' => ['active_sessions' => []]]
            );
            
            if ($result->getModifiedCount() > 0) {
                $count++;
            }
        }
        
        $totalUpdated += $count;
        
        if ($count > 0) {
            echo "✓ Added active_sessions to $count account(s) in $collectionName<br>";
        } else {
            echo "✓ All accounts in $collectionName already have the field<br>";
        }
    }
    
    echo "<hr>";
    echo "<p><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>Total student accounts: $totalAccounts</li>";
    echo "<li>Accounts updated: $totalUpdated</li>";
    echo "</ul>";
    
    if ($totalUpdated === 0) {
        echo "<p style='color: green;'>✓ All student accounts already have the active_sessions field!</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✓ Migration completed successfully! Updated $totalUpdated student account(s).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    error_log("AddActiveSessionsField error: " . $e->getMessage());
}
