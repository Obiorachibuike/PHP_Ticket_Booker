Doctor Appointment Booking System
This is a web-based application for booking doctor appointments, inspired by the functionality seen on https://doctorsapp.in/doctors/dr-kapil-shukla/8108716370. It allows users to view doctor profiles, check available time slots, and book appointments after authenticating via email and OTP.

Features
Doctor Profile Display: Shows doctor's name, qualifications, specialization, and profile images.

Clinic Details: Displays clinic name and address.

Fee Information: Shows first visit and follow-up fees.

Dynamic Slot Display: Available and booked appointment slots are fetched and displayed based on the selected date.

OTP-based User Authentication: Users can log in/register using their email address, and an OTP is sent to their email for verification.

Appointment Booking: Authenticated users can select an available slot and book an appointment.

Slot Status Update: Once an appointment is booked, the slot is marked as "booked" (greyed out).

PHPMailer Integration: Uses PHPMailer to send OTPs and can be extended for appointment confirmations.

Technologies Used
Backend: PHP

Database: MySQL

Frontend: HTML, Tailwind CSS, JavaScript

Date Picker: Flatpickr

Email Sending: PHPMailer (via SMTP)

Setup Instructions
1. Prerequisites
Before you begin, ensure you have the following installed:

Web Server: Apache (part of XAMPP, WAMP, MAMP, or standalone)

PHP: Version 7.4 or higher (included with XAMPP, WAMP, MAMP)

MySQL Database: (included with XAMPP, WAMP, MAMP)

Composer: For PHP dependency management.

Text Editor: VS Code (recommended)

2. Database Setup (MySQL)
Create Database:
Open your MySQL client (e.g., phpMyAdmin, MySQL Workbench, or command line) and create a new database. For example:

CREATE DATABASE doctors_app;
USE doctors_app;

Create Tables:
Execute the following SQL queries to create the necessary tables:

CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    qualifications VARCHAR(255),
    specialization VARCHAR(255),
    profile_image VARCHAR(255),
    logo_image VARCHAR(255)
);

CREATE TABLE clinics (
    clinic_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    in_clinic_icon VARCHAR(255),
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
);

CREATE TABLE fees (
    fee_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    clinic_id INT NOT NULL,
    first_visit_fee DECIMAL(10, 2) NOT NULL,
    follow_up_fee DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
    FOREIGN KEY (clinic_id) REFERENCES clinics(clinic_id)
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    otp VARCHAR(10),
    otp_expires_at DATETIME
);

CREATE TABLE slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    clinic_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_booked BOOLEAN DEFAULT FALSE,
    booked_by_user_id INT NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
    FOREIGN KEY (clinic_id) REFERENCES clinics(clinic_id),
    FOREIGN KEY (booked_by_user_id) REFERENCES users(user_id)
);

Insert Sample Data:
Populate your tables with some initial data:

-- Insert into doctors
INSERT INTO doctors (name, qualifications, specialization, profile_image, logo_image) VALUES
('Dr. Kapil Shukla', 'MBBS, MD', 'Pediatrician', 'https://storage.googleapis.com/a1aa/image/e03d7706-cb40-4d0e-ca6b-985695e040d4.jpg', 'https://storage.googleapis.com/a1aa/image/4b667a2b-e32d-4483-2ec5-a7db7b955ece.jpg');

-- Assuming doctor_id is 1 for Dr. Kapil Shukla
-- Insert into clinics
INSERT INTO clinics (doctor_id, name, address, in_clinic_icon) VALUES
(1, 'Radha Little Steps Pediatrics', 'Radha''s Little Steps Pediatrics clinic tata motors hatkesh Hatkesh Udhog Nagar, Konkan Division, Maharashtra, India, 401107', 'https://storage.googleapis.com/a1aa/image/7eaa604a-54b3-405e-c0c8-37c03c08cbb4.jpg');

-- Assuming clinic_id is 1 for Radha Little Steps Pediatrics
-- Insert into fees
INSERT INTO fees (doctor_id, clinic_id, first_visit_fee, follow_up_fee) VALUES
(1, 1, 600.00, 300.00);

-- Insert some sample slots (adjust dates/times as needed for current date)
INSERT INTO slots (doctor_id, clinic_id, slot_date, start_time, end_time, is_booked) VALUES
(1, 1, CURDATE(), '10:00:00', '10:30:00', FALSE),
(1, 1, CURDATE(), '10:30:00', '11:00:00', FALSE),
(1, 1, CURDATE(), '11:00:00', '11:30:00', TRUE), -- Example booked slot
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '09:30:00', FALSE),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30:00', '10:00:00', FALSE);

3. Project Files Setup
Place Files:
Place all your PHP files (index.php, login.php, process_otp.php, book_appointment.php, get_slots.php, config.php) into your web server's document root (e.g., C:\xampp\htdocs\doctors_app for XAMPP).

Configure config.php:
Open config.php and update the database connection details:

<?php
// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Your MySQL username (e.g., 'root' for XAMPP default)
define('DB_PASSWORD', '');     // Your MySQL password (e.g., '' for XAMPP default, or your password)
define('DB_NAME', 'doctors_app'); // The database name you created

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

Install PHPMailer Dependencies:
Open your VS Code terminal, navigate to your doctors_app directory, and run:

composer require phpmailer/phpmailer

This will create a vendor/ directory and composer.json/composer.lock files.

Configure PHPMailer in login.php:
Open login.php and update the SMTP server settings with your actual email service provider's details. This is crucial for sending OTPs.

// --- PHPMailer Integration for sending OTP ---
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    // IMPORTANT: Replace these with your actual SMTP server details!
    $mail->Host       = 'YOUR_SMTP_HOST';       // e.g., 'smtp.gmail.com', 'smtp.sendgrid.net'
    $mail->SMTPAuth   = true;
    // IMPORTANT: Replace with your actual SMTP username and password!
    $mail->Username   = 'YOUR_SMTP_USERNAME';   // e.g., your_email@gmail.com, 'apikey' for SendGrid
    $mail->Password   = 'YOUR_SMTP_PASSWORD';   // e.g., your Gmail App Password, your SendGrid API key
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or PHPMailer::ENCRYPTION_SMTPS
    $mail->Port       = 587;                    // or 465 (for SMTPS)
    $mail->CharSet    = 'UTF-8';

    // IMPORTANT: Use an email address that is verified with your SMTP provider as the sender.
    $mail->setFrom('no-reply@yourdoctorsapp.com', 'Doctors App');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Your Doctor Appointment OTP";
    $mail->Body    = "Your One-Time Password (OTP) for Doctor Appointment is: <b>" . htmlspecialchars($otp) . "</b><br><br>This OTP is valid for 10 minutes.";
    $mail->AltBody = "Your One-Time Password (OTP) for Doctor Appointment is: " . htmlspecialchars($otp) . "\n\nThis OTP is valid for 10 minutes.";

    $mail->send();
    $response['success'] = true;
    $response['message'] = 'OTP sent to your email. Please check your inbox (and spam folder).';
} catch (Exception $e) {
    $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    error_log("PHPMailer Error: " . $mail->ErrorInfo . " for email: " . $email);
}

4. Enable PHP zip Extension (if you encountered Composer download issues)
If Composer failed to download PHPMailer, you might need to enable the zip extension in your php.ini.

Locate your php.ini file (e.g., C:\xampp\php\php.ini).

Open it in a text editor.

Find the line ;extension=zip.

Remove the semicolon to uncomment it: extension=zip.

Save the file and restart your Apache server in XAMPP Control Panel.

Usage
Start your web server and MySQL database (e.g., via XAMPP Control Panel).

Open your web browser and navigate to the project URL: http://localhost/doctors_app/index.php

View Doctor Profile & Slots: The main page will display Dr. Kapil Shukla's profile and available appointment slots for the current date.

Select Date: Use the date picker to choose a different date and see available slots.

Login/Register: If not logged in, click "Login to Book Appointment".

Enter your email address and click "Send OTP".

Check your email inbox (and spam/junk folder) for the OTP.

Enter the received OTP and click "Verify OTP".

Upon successful verification, you will be logged in and redirected back to the appointment page.

Book Appointment:

Select an available slot by clicking on it.

Click "Continue to Book".

Confirm the booking. The slot will then appear greyed out (booked).

Troubleshooting
"Access denied for user 'root'@'localhost'": Ensure your DB_USERNAME and DB_PASSWORD in config.php are correct for your MySQL setup. For XAMPP, the default password for 'root' is usually empty ('').

"The term 'php' is not recognized": Add your PHP installation directory (e.g., C:\xampp\php) to your system's PATH environment variable and restart your terminal/VS Code.

"The term 'composer' is not recognized": Add your Composer installation directory (e.g., C:\composer) to your system's PATH environment variable and restart your terminal/VS Code.

"Message could not be sent. Mailer Error: SMTP Error: Could not connect to SMTP host.":

Verify your Host, Username, Password, SMTPSecure, and Port settings in login.php are exactly as provided by your email service provider.

Ensure the sender email in setFrom() is verified with your SMTP provider.

Check your internet connection.

If using Gmail, ensure you're using an "App Password" if 2-Step Verification is enabled.

Check your PHP error logs for more detailed PHPMailer errors.

Slots not loading: Check your browser's developer console for JavaScript errors and ensure get_slots.php is returning valid JSON data.#   P H P _ T i c k e t _ B o o k e r  
 