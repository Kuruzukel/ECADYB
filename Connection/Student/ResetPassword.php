<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['verificationCode'])) {
        echo json_encode(['success' => false, 'message' => 'Email and verification code are required']);
        exit;
    }
    
    $email = trim($input['email']);
    $verificationCode = trim($input['verificationCode']);
    
    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    if (!preg_match('/^\d{6}$/', $verificationCode)) {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code format']);
        exit;
    }
    
    // Start session to check OTP
    session_start();
    
    $otpKey = 'otp_' . $email;
    
    if (!isset($_SESSION[$otpKey])) {
        echo json_encode(['success' => false, 'message' => 'No verification code found. Please request a new code.']);
        exit;
    }
    
    $otpData = $_SESSION[$otpKey];
    
    // Check if OTP has expired
    if (time() > $otpData['expires']) {
        unset($_SESSION[$otpKey]);
        echo json_encode(['success' => false, 'message' => 'Verification code has expired. Please request a new one.']);
        exit;
    }
    
    // Check attempts limit
    if ($otpData['attempts'] >= 3) {
        unset($_SESSION[$otpKey]);
        echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please request a new verification code.']);
        exit;
    }
    
    // Verify OTP
    if ($verificationCode !== $otpData['code']) {
        $_SESSION[$otpKey]['attempts']++;
        echo json_encode(['success' => false, 'message' => 'Invalid verification code. Please try again.']);
        exit;
    }
    
    // OTP is valid, proceed with password reset
    require_once '../../Connection/Configuration/MongoConnect.php';
    
    $collection = $database->selectCollection('students');
    $student = $collection->findOne(['email' => $email]);
    
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    // Generate new password
    $newPassword = generateRandomPassword();
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $updateResult = $collection->updateOne(
        ['email' => $email],
        ['$set' => ['password' => $hashedPassword, 'updated_at' => new MongoDB\BSON\UTCDateTime()]]
    );
    
    if ($updateResult->getModifiedCount() > 0) {
        // Send new password via email
        $subject = "Your New Password - Exact Colleges of Asia";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .password-box { background: #28a745; color: white; font-size: 18px; font-weight: bold; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Password Reset Successful</h2>
                    <p>Exact Colleges of Asia - Graduation Gallery</p>
                </div>
                <div class='content'>
                    <p>Hello,</p>
                    <p>Your password has been successfully reset. Here are your new login credentials:</p>
                    <p><strong>Email:</strong> $email</p>
                    <div class='password-box'>New Password: $newPassword</div>
                    <p><strong>Important Security Notes:</strong></p>
                    <ul>
                        <li>Please change this password after your first login</li>
                        <li>Keep your login credentials secure</li>
                        <li>Do not share your password with anyone</li>
                    </ul>
                    <p>You can now log in to your Graduation Gallery account using these credentials.</p>
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
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: Exact Colleges of Asia <noreply@exactcolleges.edu.ph>',
            'Reply-To: support@exactcolleges.edu.ph',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $mailSent = mail($email, $subject, $message, implode("\r\n", $headers));
        
        // Clear OTP from session
        unset($_SESSION[$otpKey]);
        
        if ($mailSent) {
            echo json_encode([
                'success' => true, 
                'message' => 'Password reset successful. Your new password has been sent to your email.'
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Password reset successful, but failed to send email. Please contact support.'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password. Please try again.']);
    }
    
} catch (Exception $e) {
    error_log("ResetPassword error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during password reset']);
}

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $password;
}
?>
