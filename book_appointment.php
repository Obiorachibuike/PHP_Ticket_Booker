<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to book an appointment.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']);

    // Start a transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // 1. Check if the slot exists and is not already booked
        $stmt_check = $conn->prepare("SELECT is_booked FROM slots WHERE slot_id = ? FOR UPDATE"); // FOR UPDATE locks the row
        $stmt_check->bind_param("i", $slot_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $slot = $result_check->fetch_assoc();
        $stmt_check->close();

        if (!$slot) {
            throw new Exception("Selected slot does not exist.");
        }
        if ($slot['is_booked']) {
            throw new Exception("Selected slot is already booked.");
        }

        // 2. Book the slot
        $stmt_book = $conn->prepare("UPDATE slots SET is_booked = TRUE, booked_by_user_id = ? WHERE slot_id = ?");
        $stmt_book->bind_param("ii", $user_id, $slot_id);
        $stmt_book->execute();

        if ($stmt_book->affected_rows === 1) {
            $conn->commit(); // Commit the transaction
            $response['success'] = true;
            $response['message'] = 'Appointment booked successfully!';

            // In a real application, you might also:
            // - Send a confirmation email to the user
            // - Add booking details to a separate 'appointments' table if needed
        } else {
            throw new Exception("Failed to book appointment. Please try again.");
        }
        $stmt_book->close();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        $response['message'] = $e->getMessage();
    }

} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
$conn->close();
?>