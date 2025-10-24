<?php
// Suppress all output except JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email']) || empty($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $email = trim($input['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    require_once __DIR__ . '/../Configuration/MongoConnect.php';

    $database = $GLOBALS['database'] ?? null;
    if (!$database) {
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
        exit;
    }

    $user = null;
    
    // First, check admin accounts collection
    try {
        $mongoClient = $GLOBALS['mongoClient'] ?? null;
        if (!$mongoClient) {
            // Fallback: create new client if global not available
            require_once __DIR__ . '/../../vendor/autoload.php';
            $mongoClient = new \MongoDB\Client("mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957/");
        }
        $adminDB = $mongoClient->admin;
        $adminCollection = $adminDB->accounts;
        
        $user = $adminCollection->findOne(['email' => $email]);
    } catch (Exception $e) {
        error_log("Admin check error: " . $e->getMessage());
    }
    
    // If not found in admin, search student department collections
    if (!$user) {
        // Define all department collections to search
        $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];
        
        // Search through each department collection
        foreach ($departmentCollections as $collectionName) {
            $collection = $database->selectCollection($collectionName);
            $user = $collection->findOne(['email' => $email]);
            
            if ($user) {
                // Found the student, no need to search further
                break;
            }
        }
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found in database']);
        exit;
    }

    $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['otp_' . $email] = [
        'code' => $otp,
        'expires' => time() + 300,
        'attempts' => 0
    ];

    $subject = "Password Reset Verification Code - Exact Colleges of Asia";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .otp-code { background: #6366f1; color: white; font-size: 24px; font-weight: bold; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0; letter-spacing: 3px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Password Reset Verification</h2>
                <p>Exact Colleges of Asia - Graduation Gallery</p>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>You have requested to reset your password for your Graduation Gallery account.</p>
                <p>Please use the following verification code to complete your password reset:</p>
                <div class='otp-code'>$otp</div>
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This code will expire in 5 minutes</li>
                    <li>Do not share this code with anyone</li>
                    <li>If you didn't request this reset, please ignore this email</li>
                </ul>
                <p>If you have any questions, please contact our support team.</p>
                <p>Best regards,<br>Exact Colleges of Asia</p>
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Load email configuration
    require_once __DIR__ . '/../Configuration/EmailConfig.php';
    
    // Use PHPMailer for Gmail SMTP
    $mail = new PHPMailer(true);
    
    try {
        // Gmail SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = GMAIL_SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_USERNAME;
        $mail->Password   = GMAIL_APP_PASSWORD;
        $mail->SMTPSecure = GMAIL_SMTP_ENCRYPTION;
        $mail->Port       = GMAIL_SMTP_PORT;
        
        // Email settings
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        
        echo json_encode([
            'success' => true,
            'message' => 'Verification code sent successfully to your email',
            'otp' => $otp,
            'email_sent' => true
        ]);
        
    } catch (Exception $e) {
        // Email sending failed - fall back to display mode for testing
        error_log("PHPMailer Error: {$mail->ErrorInfo}. OTP: $otp");
        
        echo json_encode([
            'success' => true,
            'message' => 'Email service unavailable. Your verification code is displayed in the notification.',
            'otp' => $otp,
            'email_sent' => false,
            'error' => $mail->ErrorInfo
        ]);
    }
} catch (Exception $e) {
    error_log("SendOTP error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the verification code']);
}
