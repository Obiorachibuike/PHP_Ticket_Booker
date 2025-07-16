<?php
session_start();
require_once 'config.php'; // Include database connection

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['otp'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $otp = $_POST['otp'];
    $current_time = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT user_id, otp, otp_expires_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user['otp'] === $otp && $current_time <= $user['otp_expires_at']) {
            // OTP is valid
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_email'] = $email;

            // Clear OTP after successful login
            $stmt_clear_otp = $conn->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE user_id = ?");
            $stmt_clear_otp->bind_param("i", $user['user_id']);
            $stmt_clear_otp->execute();
            $stmt_clear_otp->close();

            $response['success'] = true;
            $response['message'] = 'Login successful!';
        } elseif ($current_time > $user['otp_expires_at']) {
            $response['message'] = 'OTP has expired. Please request a new one.';
        } else {
            $response['message'] = 'Invalid OTP.';
        }
    } else {
        $response['message'] = 'Email not found.';
    }

} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
$conn->close();
?>