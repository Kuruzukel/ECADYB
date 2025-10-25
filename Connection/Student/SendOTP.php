<?php
// Ensure JSON is always returned
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
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
        'maxTimeMS' => 2000, // 2 second timeout per query
        'projection' => ['email' => 1, '_id' => 1] // Only fetch needed fields
    ];

    // First, check admin accounts collection
    try {
        $mongoClient = $GLOBALS['mongoClient'] ?? null;
        if (!$mongoClient) {
            // Fallback: create new client if global not available
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

    // If not found in admin, search student department collections
    if (!$user) {
        // Define all department collections to search
        $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];

        // Search with optimized timeout - stop at first match
        foreach ($departmentCollections as $collectionName) {
            try {
                $collection = $database->selectCollection($collectionName);
                $user = $collection->findOne(['email' => $email], $queryOptions);

                if ($user) {
                    // Found the student, no need to search further
                    break;
                }
            } catch (Exception $e) {
                // Log timeout or error and continue to next collection
                error_log("Collection $collectionName search error: " . $e->getMessage());
                continue;
            }
        }
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found in database']);
        exit;
    }

    // Generate OTP with safe random number generation
    try {
        // Try random_int first (PHP 7+, cryptographically secure)
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        // Fallback to mt_rand if random_int fails
        error_log("random_int failed, using mt_rand: " . $e->getMessage());
        $otp = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['otp_' . $email] = [
        'code' => $otp,
        'expires' => time() + 120,
        'attempts' => 0
    ];

    // IMPORTANT: Remove 'otp' from response in PRODUCTION for security
    echo json_encode([
        'success' => true,
        'message' => 'Verification code is being sent to your email',
        // 'otp' => $otp, // DEVELOPMENT ONLY - Uncomment for testing on localhost
        'email_sent' => true
    ]);

    // Force output to be sent to client immediately
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();

    // Close session to release lock (important for concurrent requests)
    if (session_status() == PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    // For FastCGI, finish the request to the client
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    // NOW send email in background after client has received response
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
                        <li>This code will expire in 120 seconds</li>
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

        // Load email configuration from environment variables (Railway) or config file (localhost)
        // Priority: Environment Variables > EmailConfig.php > Fallback defaults

        // Check for REAL environment variables (Railway deployment) - NOT $_ENV which can be polluted
        // Only use getenv() to avoid $_ENV pollution from sample files
        $smtpHost = getenv('SMTP_HOST') ?: null;
        $smtpPort = getenv('SMTP_PORT') ?: null;
        $smtpUsername = getenv('SMTP_USERNAME') ?: null;
        $smtpPassword = getenv('SMTP_PASSWORD') ?: null;
        $smtpFromEmail = getenv('SMTP_FROM_EMAIL') ?: null;
        $smtpFromName = getenv('SMTP_FROM_NAME') ?: null;
        $smtpEncryption = getenv('SMTP_ENCRYPTION') ?: 'tls';

        // If environment variables not set, try loading from EmailConfig.php (localhost)
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

        // Validate email configuration
        if (!$smtpHost || !$smtpUsername || !$smtpPassword) {
            error_log("ERROR: Email configuration incomplete. SMTP_HOST: " . ($smtpHost ? "set" : "missing") .
                ", SMTP_USERNAME: " . ($smtpUsername ? "set" : "missing") .
                ", SMTP_PASSWORD: " . ($smtpPassword ? "set" : "missing"));
            return; // Skip email sending if configuration is incomplete
        }

        error_log("Email config loaded - Host: $smtpHost, Port: $smtpPort, User: $smtpUsername");

        // Use PHPMailer for Gmail SMTP
        $mail = new PHPMailer(true);

        try {
            // Detect if running on Railway or localhost
            $isRailway = (getenv('RAILWAY_ENVIRONMENT') || getenv('RAILWAY_PUBLIC_URL')) ? true : false;

            // SMTP Configuration using variables from environment or config file
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUsername;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = ($smtpEncryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$smtpPort;

            // Timeout settings - more lenient for Railway
            $mail->Timeout = $isRailway ? 30 : 15; // Railway needs more time
            $mail->SMTPKeepAlive = false;

            // Debug output - disable in production (set to 0), enable (2) for troubleshooting
            $mail->SMTPDebug = 0; // 0 = off, 2 = verbose
            $mail->Debugoutput = function ($str, $level) {
                error_log("SMTP Debug ($level): $str");
            };

            // SSL/TLS options - Railway-compatible (more lenient)
            if ($isRailway) {
                // Railway needs less strict SSL verification
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                error_log("Using Railway-compatible SSL settings");
            } else {
                // Localhost uses proper SSL verification
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => false
                    )
                );
                error_log("Using standard SSL settings for localhost");
            }

            // Email settings using variables
            $mail->setFrom($smtpFromEmail, $smtpFromName);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message)); // Plain text version
            $mail->CharSet = 'UTF-8';

            // Send email with better error handling
            $emailSent = false;
            $lastError = '';

            try {
                $emailSent = $mail->send();
                error_log("Email sent successfully to: $email");
            } catch (Exception $e) {
                $lastError = $mail->ErrorInfo;
                error_log("PHPMailer send failed: {$mail->ErrorInfo}");
                error_log("Exception details: " . $e->getMessage());
            }

            if (!$emailSent) {
                error_log("Email failed to send. Last error: $lastError");
                // You could also update the response here if needed
            }
        } catch (Exception $e) {
            error_log("PHPMailer configuration error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    };

    // Execute the email send function (response already sent above)
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
