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

    require_once '../../Connection/Configuration/MongoConnect.php';

    $collection = $database->selectCollection('students');
    $student = $collection->findOne(['email' => $email]);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Email not found in database']);
        exit;
    }

    $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

    session_start();
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

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Exact Colleges of Asia <noreply@exactcolleges.edu.ph>',
        'Reply-To: support@exactcolleges.edu.ph',
        'X-Mailer: PHP/' . phpversion()
    ];

    $mailSent = mail($email, $subject, $message, implode("\r\n", $headers));

    if ($mailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Verification code sent successfully',
            'otp' => $otp
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again.']);
    }
} catch (Exception $e) {
    error_log("SendOTP error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the verification code']);
}
