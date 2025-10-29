<?php
/**
 * Migration Script: Add active_sessions field to admin accounts
 * 
 * This script adds the active_sessions field to all admin accounts
 * in the admin.accounts collection if it doesn't exist.
 * 
 * Usage: Run this file once via browser or CLI
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/EnvLoader.php';

use MongoDB\Client;

try {
    echo "<h2>Adding active_sessions field to admin accounts...</h2>";
    
    $client = new Client(getMongoUrl());
    $adminDB = $client->selectDatabase('admin');
    $accountsCollection = $adminDB->selectCollection('accounts');
    
    // Find all accounts without active_sessions field
    $accountsWithoutField = $accountsCollection->find([
        'active_sessions' => ['$exists' => false]
    ]);
    
    $count = 0;
    foreach ($accountsWithoutField as $account) {
        $result = $accountsCollection->updateOne(
            ['_id' => $account['_id']],
            ['$set' => ['active_sessions' => []]]
        );
        
        if ($result->getModifiedCount() > 0) {
            $count++;
            echo "✓ Added active_sessions to account: " . ($account['email'] ?? $account['username'] ?? 'Unknown') . "<br>";
        }
    }
    
    if ($count === 0) {
        echo "<p style='color: green;'>✓ All admin accounts already have the active_sessions field!</p>";
    } else {
        echo "<p style='color: green;'>✓ Successfully added active_sessions field to $count admin account(s)!</p>";
    }
    
    // Verify the update
    $totalAccounts = $accountsCollection->countDocuments([]);
    $accountsWithField = $accountsCollection->countDocuments([
        'active_sessions' => ['$exists' => true]
    ]);
    
    echo "<hr>";
    echo "<p><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>Total admin accounts: $totalAccounts</li>";
    echo "<li>Accounts with active_sessions field: $accountsWithField</li>";
    echo "</ul>";
    
    if ($totalAccounts === $accountsWithField) {
        echo "<p style='color: green; font-weight: bold;'>✓ Migration completed successfully!</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Some accounts may still be missing the field. Please run this script again.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    error_log("AddActiveSessionsField error: " . $e->getMessage());
}
