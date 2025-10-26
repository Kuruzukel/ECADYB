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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load dependencies']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $adminDB = $mongoClient->admin;
        $adminCollection = $adminDB->accounts;

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

                if ($user) {
                    break;
                }
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

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['otp_' . $email] = [
        'code' => $otp,
        'expires' => time() + 60,
        'attempts' => 0
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Verification code is being sent to your email',
        'email_sent' => true
    ]);

    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();

    if (session_status() == PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    $asyncEmailSend = function () use ($email, $otp) {
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
                        <li>This code will expire in 60 seconds</li>
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

        require_once __DIR__ . '/../Configuration/EnvLoader.php';

        $smtpHost = getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? null);
        $smtpPort = getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? null);
        $smtpUsername = getenv('SMTP_USERNAME') ?: ($_ENV['SMTP_USERNAME'] ?? null);
        $smtpPassword = getenv('SMTP_PASSWORD') ?: ($_ENV['SMTP_PASSWORD'] ?? null);
        $smtpFromEmail = getenv('SMTP_FROM_EMAIL') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? null);
        $smtpFromName = getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? null);
        $smtpEncryption = getenv('SMTP_ENCRYPTION') ?: ($_ENV['SMTP_ENCRYPTION'] ?? 'tls');

        if (!$smtpHost || !$smtpUsername || !$smtpPassword) {
            $emailConfigPath = null;
            $possiblePaths = [
                __DIR__ . '/../Configuration/EmailConfig.php',
                dirname(__DIR__) . '/Configuration/EmailConfig.php',
                $_SERVER['DOCUMENT_ROOT'] . '/Connection/Configuration/EmailConfig.php',
                realpath(__DIR__ . '/../Configuration/EmailConfig.php')
            ];

            foreach ($possiblePaths as $path) {
                if ($path && file_exists($path)) {
                    $emailConfigPath = $path;
                    break;
                }
            }

            if ($emailConfigPath) {
                require_once $emailConfigPath;
                $smtpHost = $smtpHost ?? (defined('GMAIL_SMTP_HOST') ? GMAIL_SMTP_HOST : 'smtp.gmail.com');
                $smtpPort = $smtpPort ?? (defined('GMAIL_SMTP_PORT') ? GMAIL_SMTP_PORT : 587);
                $smtpUsername = $smtpUsername ?? (defined('GMAIL_USERNAME') ? GMAIL_USERNAME : '');
                $smtpPassword = $smtpPassword ?? (defined('GMAIL_APP_PASSWORD') ? GMAIL_APP_PASSWORD : '');
                $smtpFromEmail = $smtpFromEmail ?? (defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : '');
                $smtpFromName = $smtpFromName ?? (defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : 'Exact Colleges of Asia');
                $smtpEncryption = $smtpEncryption ?? (defined('GMAIL_SMTP_ENCRYPTION') ? GMAIL_SMTP_ENCRYPTION : 'tls');
            } else {
                error_log("WARNING: Email configuration not found in environment or config file");
            }
        }

        if (!$smtpHost || !$smtpUsername || !$smtpPassword) {
            error_log("✗ ERROR: Email configuration incomplete. SMTP_HOST: " . ($smtpHost ? "set" : "missing") .
                ", SMTP_USERNAME: " . ($smtpUsername ? "set" : "missing") .
                ", SMTP_PASSWORD: " . ($smtpPassword ? "set" : "missing"));
            return;
        }

        error_log("✓ Email config loaded - Host: $smtpHost, Port: $smtpPort, User: $smtpUsername, Encryption: $smtpEncryption");

        $mail = new PHPMailer(true);

        try {
            $isRailway = (getenv('RAILWAY_ENVIRONMENT') || getenv('RAILWAY_PUBLIC_URL')) ? true : false;
            error_log("Environment detected: " . ($isRailway ? "Railway" : "Localhost"));

            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUsername;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = ($smtpEncryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$smtpPort;

            $mail->Timeout = $isRailway ? 60 : 15;
            $mail->SMTPKeepAlive = false;

            $mail->SMTPDebug = $isRailway ? 2 : 0;
            $mail->Debugoutput = function ($str, $level) {
                error_log("SMTP Debug ($level): $str");
            };

            if ($isRailway) {
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                error_log("Using Railway-compatible SSL settings for email: $email");
            } else {
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => false
                    )
                );
                error_log("Using standard SSL settings for localhost for email: $email");
            }

            $mail->setFrom($smtpFromEmail, $smtpFromName);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message)); // Plain text version
            $mail->CharSet = 'UTF-8';

            $emailSent = false;
            $lastError = '';

            error_log("Attempting to send email to: $email via SMTP: $smtpHost:$smtpPort");

            try {
                $emailSent = $mail->send();
                error_log("✓ Email sent successfully to: $email (OTP: $otp)");
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo;
                error_log("✗ PHPMailer send failed for $email");
                error_log("Error Info: {$mail->ErrorInfo}");
                error_log("Exception: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
            }

            if (!$emailSent) {
                error_log("✗ FINAL STATUS: Email failed to send to $email. Last error: $lastError");
            }
        } catch (Exception $e) {
            error_log("PHPMailer configuration error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    };

    $asyncEmailSend();
} catch (Exception $e) {
    error_log("SendOTP error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the verification code', 'error' => $e->getMessage()]);
} catch (Error $e) {
    error_log("SendOTP fatal error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Fatal error occurred', 'error' => $e->getMessage()]);
}
