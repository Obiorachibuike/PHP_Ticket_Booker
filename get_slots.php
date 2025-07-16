<?php
require_once 'config.php';

header('Content-Type: application/json');

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$clinic_id = isset($_GET['clinic_id']) ? intval($_GET['clinic_id']) : 0;
$appointment_date = isset($_GET['appointment_date']) ? $_GET['appointment_date'] : '';

// Validate inputs
if (!$doctor_id || !$clinic_id || !preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $appointment_date)) {
    echo json_encode([]); // Return empty array if inputs are invalid
    exit();
}

// Convert date format from m/d/Y to Y-m-d for MySQL
$formatted_date = DateTime::createFromFormat('m/d/Y', $appointment_date)->format('Y-m-d');

$stmt = $conn->prepare("SELECT slot_id, start_time, end_time, is_booked FROM slots WHERE doctor_id = ? AND clinic_id = ? AND slot_date = ? ORDER BY start_time");
$stmt->bind_param("iis", $doctor_id, $clinic_id, $formatted_date);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}

echo json_encode($slots);

$stmt->close();
$conn->close();
?>