<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load dependencies']);
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
    $queryOptions = [
        'maxTimeMS' => 2000,
        'projection' => ['email' => 1, '_id' => 1]
    ];

    try {
        $mongoClient = $GLOBALS['mongoClient'] ?? null;
        if (!$mongoClient) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            require_once __DIR__ . '/../Configuration/EnvLoader.php';
            $mongoClient = new \MongoDB\Client(getMongoUrl());
        }
        $adminDB = $mongoClient->selectDatabase('admin');
        $adminCollection = $adminDB->selectCollection('accounts');
        $user = $adminCollection->findOne(['email' => $email], $queryOptions);
    } catch (Exception $e) {
        error_log("Admin check error: " . $e->getMessage());
    }

    if (!$user) {
        $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];
        foreach ($departmentCollections as $collectionName) {
            try {
                $collection = $database->selectCollection($collectionName);
                $user = $collection->findOne(['email' => $email], $queryOptions);
                if ($user) break;
            } catch (Exception $e) {
                error_log("Collection $collectionName search error: " . $e->getMessage());
                continue;
            }
        }
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found in database']);
        exit;
    }

    try {
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        error_log("random_int failed, using mt_rand: " . $e->getMessage());
        $otp = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    try {
        $mongoClient = $GLOBALS['mongoClient'] ?? null;
        if (!$mongoClient) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            require_once __DIR__ . '/../Configuration/EnvLoader.php';
            $mongoClient = new \MongoDB\Client(getMongoUrl());
        }

        $otpDB = $mongoClient->selectDatabase('ECADYB');
        $otpCollection = $otpDB->selectCollection('otp_codes');

        // Delete any existing OTP for this email
        $otpCollection->deleteOne(['email' => $email]);

        // Insert new OTP
        $otpCollection->insertOne([
            'email' => $email,
            'code' => $otp,
            'expires' => time() + 600, // 10 minutes expiry
            'attempts' => 0,
            'created_at' => new \MongoDB\BSON\UTCDateTime()
        ]);

        error_log("OTP stored in MongoDB for email: $email");
    } catch (Exception $e) {
        error_log("Failed to store OTP in MongoDB: " . $e->getMessage());
        // Continue anyway, we'll still send the email
    }

    // Also store in session as fallback for local development
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['otp_' . $email] = [
        'code' => $otp,
        'expires' => time() + 600,
        'attempts' => 0
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Verification code is being sent to your email',
        'email_sent' => true,
        'otp' => $otp
    ]);

    if (ob_get_level() > 0) ob_end_flush();
    flush();
    if (session_status() == PHP_SESSION_ACTIVE) session_write_close();
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    $sendGridApiKey = getenv('SENDGRID_API_KEY') ?: ($_ENV['SENDGRID_API_KEY'] ?? null);
    $fromEmail = getenv('SMTP_FROM_EMAIL') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? 'admain.ecadyb@gmail.com');
    $fromName = getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? 'Graduation Gallery');

    $htmlContent = "
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
                    <li>This code will expire in 10 minutes</li>
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

    // Try SendGrid first (for Railway), fallback to PHPMailer (for localhost)
    $emailSent = false;

    if ($sendGridApiKey && $sendGridApiKey !== 'your-sendgrid-api-key-here') {
        try {
            $emailContent = new \SendGrid\Mail\Mail();
            $emailContent->setFrom($fromEmail, $fromName);
            $emailContent->setSubject("Password Reset Verification Code - Exact Colleges of Asia");
            $emailContent->addTo($email);
            $emailContent->addContent("text/html", $htmlContent);

            $sendgrid = new \SendGrid($sendGridApiKey);
            $response = $sendgrid->send($emailContent);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                error_log("✓ SendGrid email sent successfully to: $email (Status: " . $response->statusCode() . ")");
                $emailSent = true;
            }
        } catch (Exception $e) {
            error_log("✗ SendGrid error: " . $e->getMessage());
        }
    }

    // Fallback to PHPMailer (Gmail SMTP) if SendGrid fails or not configured
    if (!$emailSent) {
        try {
            require_once __DIR__ . '/../Configuration/EmailConfig.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = GMAIL_SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = GMAIL_USERNAME;
            $mail->Password = GMAIL_APP_PASSWORD;
            $mail->SMTPSecure = GMAIL_SMTP_ENCRYPTION;
            $mail->Port = GMAIL_SMTP_PORT;

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Verification Code - Exact Colleges of Asia";
            $mail->Body = $htmlContent;

            $mail->send();
            error_log("✓ PHPMailer (Gmail) email sent successfully to: $email");
            $emailSent = true;
        } catch (Exception $e) {
            error_log("✗ PHPMailer error: " . $e->getMessage());
        }
    }

    if (!$emailSent) {
        error_log("✗ Failed to send email using both SendGrid and PHPMailer");
    }
} catch (Exception $e) {
    error_log("SendOTP error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
