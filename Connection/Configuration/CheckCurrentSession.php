<?php

/**
 * Check Current Session Data
 */

session_start();

echo "=================================================\n";
echo "CURRENT SESSION DATA\n";
echo "=================================================\n\n";

echo "Student ID: " . ($_SESSION['student_id'] ?? 'NOT SET') . "\n";
echo "Name: " . ($_SESSION['name'] ?? 'NOT SET') . "\n";
echo "Department: " . ($_SESSION['department'] ?? 'NOT SET') . "\n";
echo "Academic Year: " . ($_SESSION['academic_year'] ?? 'NOT SET ❌') . "\n";
echo "Section: " . ($_SESSION['section'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";

echo "\n=================================================\n";

if (empty($_SESSION['academic_year'])) {
    echo "⚠ WARNING: academic_year is NOT set!\n";
    echo "Please LOGOUT and LOGIN again to fix this.\n";
} else {
    echo "✅ SUCCESS: academic_year is set!\n";
    echo "The completion date feature should work now.\n";
}

echo "=================================================\n";
