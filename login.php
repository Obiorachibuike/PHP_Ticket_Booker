<?php
session_start();
require_once 'config.php'; // Include database connection

// Require Composer's autoloader
// This line should be at the very top of your PHP file if using Composer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Needed if you explicitly use SMTP class constants

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit();
    }

    // Generate a 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, update OTP
        $stmt_update = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $otp, $otp_expires_at, $email);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // New user, insert
        $stmt_insert = $conn->prepare("INSERT INTO users (email, otp, otp_expires_at) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $email, $otp, $otp_expires_at);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    // --- PHPMailer Integration for sending OTP ---
    $mail = new PHPMailer(true); // Passing `true` enables exceptions

    try {
        // Server settings
        $mail->isSMTP();                                            // Send using SMTP
        // IMPORTANT: Replace these with your actual SMTP server details!
        // Examples: 'smtp.gmail.com', 'smtp.sendgrid.net', 'smtp.mailgun.org', 'smtp.office365.com'
        $mail->Host       = 'smtp.gmail.com';                     // Your SMTP server host
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        // IMPORTANT: Replace with your actual SMTP username and password!
        // For Gmail, often your email and an App Password. For other services, usually an API key or specific credentials.
        $mail->Username   = 'obiorachibuike22@gmail.com';               // Your SMTP username (e.g., your email address)
        $mail->Password   = 'nfxh tthc iaxy nlsu';                  // Your SMTP password or API key
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to; use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $mail->CharSet    = 'UTF-8';                                // Set character set for the email

        // Recipients
        // IMPORTANT: Use an email address that is verified with your SMTP provider as the sender.
        $mail->setFrom('no-reply@yourdoctorsapp.com', 'Doctors App'); // Sender's email and name.
        $mail->addAddress($email);                                  // Add a recipient (the user's email)

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = "Your Doctor Appointment OTP";
        $mail->Body    = "Your One-Time Password (OTP) for Doctor Appointment is: <b>" . htmlspecialchars($otp) . "</b><br><br>This OTP is valid for 10 minutes.";
        $mail->AltBody = "Your One-Time Password (OTP) for Doctor Appointment is: " . htmlspecialchars($otp) . "\n\nThis OTP is valid for 10 minutes.";

        $mail->send();
        $response['success'] = true;
        $response['message'] = 'OTP sent to your email. Please check your inbox (and spam folder).';
    } catch (Exception $e) {
        $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        // Log the detailed error for debugging purposes
        error_log("PHPMailer Error: " . $mail->ErrorInfo . " for email: " . $email);
    }

} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
$conn->close();
?>
