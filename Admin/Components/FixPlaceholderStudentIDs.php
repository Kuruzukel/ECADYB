<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ' . BASE_URL . 'Login');
  exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$message = '';
$messageType = '';
$studentsWithPlaceholder = [];

try {
  require_once __DIR__ . '/../../Connection/Configuration/EnvLoader.php';
  $mongoUrl = getMongoUrl();
  $client = new Client($mongoUrl);
  
  $dbName = "ECADYB";
  $db = $client->$dbName;
  
  $collections = [
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
  
  // Scan for students with placeholder IDs
  foreach ($collections as $collName => $fullName) {
    $collection = $db->$collName;
    
    $placeholderStudents = $collection->find([
      '$or' => [
        ['student id' => '0000-000000'],
        ['student_id' => '0000-000000']
      ]
    ]);
    
    foreach ($placeholderStudents as $student) {
      $studentsWithPlaceholder[] = [
        'collection' => $collName,
        'program' => $fullName,
        '_id' => (string)$student['_id'],
        'first_name' => $student['first name'] ?? 'N/A',
        'last_name' => $student['last name'] ?? 'N/A',
        'student_id_field' => $student['student id'] ?? $student['student_id'] ?? 'N/A',
        'academic_year' => $student['academic year'] ?? 'N/A',
        'section' => $student['department section'] ?? 'N/A'
      ];
    }
  }
  
  // Handle fix request
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_all'])) {
    $fixedCount = 0;
    
    foreach ($studentsWithPlaceholder as $student) {
      $collection = $db->{$student['collection']};
      
      // Generate a proper student ID based on program and ID counter
      $programCode = strtoupper($student['collection']);
      $year = substr($student['academic_year'], 2, 2) ?? date('y');
      $nextId = str_pad($collection->countDocuments() + 1, 4, '0', STR_PAD_LEFT);
      $newStudentId = "{$year}{$programCode}-{$nextId}";
      
      try {
        $result = $collection->updateOne(
          ['_id' => new ObjectId($student['_id'])],
          ['$set' => ['student id' => $newStudentId]]
        );
        
        if ($result->getModifiedCount() > 0) {
          $fixedCount++;
          error_log("Fixed student ID: {$student['first_name']} {$student['last_name']} - New ID: {$newStudentId}");
        }
      } catch (Exception $e) {
        error_log("Error fixing student ID for " . $student['_id'] . ": " . $e->getMessage());
      }
    }
    
    $message = "Successfully fixed {$fixedCount} student records!";
    $messageType = 'success';
    
    // Refresh the list
    $studentsWithPlaceholder = [];
    foreach ($collections as $collName => $fullName) {
      $collection = $db->$collName;
      $placeholderStudents = $collection->find([
        '$or' => [
          ['student id' => '0000-000000'],
          ['student_id' => '0000-000000']
        ]
      ]);
      
      foreach ($placeholderStudents as $student) {
        $studentsWithPlaceholder[] = [
          'collection' => $collName,
          'program' => $fullName,
          '_id' => (string)$student['_id'],
          'first_name' => $student['first name'] ?? 'N/A',
          'last_name' => $student['last name'] ?? 'N/A',
          'student_id_field' => $student['student id'] ?? $student['student_id'] ?? 'N/A',
          'academic_year' => $student['academic year'] ?? 'N/A',
          'section' => $student['department section'] ?? 'N/A'
        ];
      }
    }
  }
  
} catch (Exception $e) {
  $message = "Error: " . $e->getMessage();
  $messageType = 'error';
  error_log("Error in FixPlaceholderStudentIDs.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fix Placeholder Student IDs - Admin</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Admin/assets/css/AdminDashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    .fix-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
    }
    
    .page-header {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      padding: 30px;
      border-radius: 15px;
      color: white;
      margin-bottom: 30px;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }
    
    .page-header h1 {
      margin: 0 0 10px 0;
      font-size: 28px;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .page-header p {
      margin: 0;
      opacity: 0.9;
      font-size: 16px;
    }
    
    .stats-box {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 25px;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .stat-item {
      text-align: center;
      padding: 20px;
      background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
      border-radius: 10px;
      border: 2px solid #e5e7eb;
    }
    
    .stat-number {
      font-size: 36px;
      font-weight: bold;
      color: #ef4444;
      margin-bottom: 5px;
    }
    
    .stat-label {
      color: #6b7280;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .students-table {
      width: 100%;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .students-table table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .students-table th {
      background: #1f2937;
      color: white;
      padding: 15px;
      text-align: left;
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .students-table td {
      padding: 15px;
      border-bottom: 1px solid #e5e7eb;
      color: #374151;
    }
    
    .students-table tr:hover {
      background: #f9fafb;
    }
    
    .placeholder-badge {
      background: #fef3c7;
      color: #92400e;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }
    
    .fix-btn {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      border: none;
      padding: 15px 30px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
      transition: all 0.3s ease;
    }
    
    .fix-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    
    .fix-btn:disabled {
      background: #9ca3af;
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
    }
    
    .message {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-weight: 500;
    }
    
    .message.success {
      background: #d1fae5;
      color: #065f46;
      border: 2px solid #10b981;
    }
    
    .message.error {
      background: #fee2e2;
      color: #991b1b;
      border: 2px solid #ef4444;
    }
    
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #3b82f6;
      text-decoration: none;
      font-weight: 600;
      margin-bottom: 20px;
    }
    
    .back-link:hover {
      color: #2563eb;
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .empty-state i {
      font-size: 64px;
      color: #10b981;
      margin-bottom: 20px;
    }
    
    .empty-state h3 {
      color: #1f2937;
      margin-bottom: 10px;
    }
    
    .empty-state p {
      color: #6b7280;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/Header.php'; ?>
  
  <div class="fix-container">
    <a href="<?php echo BASE_URL; ?>Admin/Components/AdminDashboard.php" class="back-link">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
    <div class="page-header">
      <h1>
        <i class="fas fa-tools"></i>
        Fix Placeholder Student IDs
      </h1>
      <p>Scan and fix student records with placeholder ID "0000-000000"</p>
    </div>
    
    <?php if ($message): ?>
      <div class="message <?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    
    <?php if (count($studentsWithPlaceholder) > 0): ?>
      <div class="stats-box">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number"><?php echo count($studentsWithPlaceholder); ?></div>
            <div class="stat-label">Students with Placeholder ID</div>
          </div>
        </div>
        
        <form method="POST" onsubmit="return confirm('Are you sure you want to fix all placeholder student IDs? This will generate new IDs for all affected students.');">
          <button type="submit" name="fix_all" class="fix-btn">
            <i class="fas fa-magic"></i> Fix All Placeholder IDs
          </button>
        </form>
      </div>
      
      <div class="students-table">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Current ID</th>
              <th>Program</th>
              <th>Academic Year</th>
              <th>Section</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($studentsWithPlaceholder as $student): ?>
              <tr>
                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                <td><span class="placeholder-badge"><?php echo htmlspecialchars($student['student_id_field']); ?></span></td>
                <td><?php echo htmlspecialchars($student['program']); ?></td>
                <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                <td><?php echo htmlspecialchars($student['section']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-check-circle"></i>
        <h3>All Clear!</h3>
        <p>No students found with placeholder student IDs. All records are in good shape.</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

