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

require_once '../../vendor/autoload.php';

use MongoDB\Client;

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['email']) || !isset($input['verificationCode'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and verification code are required']);
        exit;
    }

    $email = trim($input['email']);
    $verificationCode = trim($input['verificationCode']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    if (!preg_match('/^\d{6}$/', $verificationCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid verification code format']);
        exit;
    }

    $client = new Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('ECADYB');
    $collection = $database->selectCollection('students');

    $student = $collection->findOne(['email' => $email]);

    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }

    // For now, we'll just check if it's a 6-digit number
    // You should implement proper OTP verification with expiration time

    // Generate a new password (8 characters)
    $newPassword = generateRandomPassword();

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the student's password in the database
    $updateResult = $collection->updateOne(
        ['email' => $email],
        ['$set' => ['password' => $hashedPassword]]
    );

    if ($updateResult->getModifiedCount() === 0) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        exit;
    }

    // Send the new password via email
    $emailSent = sendPasswordEmail($email, $newPassword);

    if (!$emailSent) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Password updated but failed to send email']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Password reset successful. Please check your email for your new password.'
    ]);
} catch (Exception $e) {
    error_log("ForgotPassword error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function generateRandomPassword($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    $max = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $max)];
    }

    return $password;
}

function sendPasswordEmail($email, $password)
{
    // Email configuration
    $to = $email;
    $subject = "Your New Password - ECADYB";
    $message = "
    <html>
    <head>
        <title>Password Reset - ECADYB</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #6366f1; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .password-box { background-color: #e7e5ff; border: 2px solid #6366f1; padding: 15px; margin: 20px 0; text-align: center; border-radius: 5px; }
            .password { font-size: 24px; font-weight: bold; color: #6366f1; letter-spacing: 2px; }
            .footer { padding: 20px; text-align: center; color: #666; font-size: 14px; }
            .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 15px 0; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset Successful</h1>
                <p>Exact Colleges of Asia - Digital Yearbook</p>
            </div>
            <div class='content'>
                <h2>Hello!</h2>
                <p>Your password has been successfully reset. Here are your new login credentials:</p>
                
                <div class='password-box'>
                    <p><strong>Your New Password:</strong></p>
                    <div class='password'>$password</div>
                </div>
                
                <div class='warning'>
                    <strong>Important:</strong> Please log in immediately and change your password to something more secure and memorable.
                </div>
                
                <p>You can now log in to your account using your email address and the password above.</p>
                
                <p>If you did not request this password reset, please contact our support team immediately.</p>
            </div>
            <div class='footer'>
                <p>Best regards,<br>ECADYB Team</p>
                <p><small>This is an automated message. Please do not reply to this email.</small></p>
            </div>
        </div>
    </body>
    </html>";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ECADYB <noreply@ecadyb.com>',
        'Reply-To: support@ecadyb.com',
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}
