<?php
require "DataBase.php"; // Include your database connection file
require 'vendor/autoload.php'; // Include Composer autoload for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json'); // Set response header to JSON

// Function to send OTP via email using PHPMailer
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP(); // Use SMTP
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'gilbert.dolz24@gmail.com'; // Your Gmail address
        $mail->Password = 'jxgx vobl ozvi caoc'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to (587 for TLS)

        // Recipients
        $mail->setFrom('gilbert.dolz24@gmail.com', 'Delight'); // Set the "From" address
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body = "Your OTP for password reset is: $otp";

        $mail->send(); // Send the email
        return true; // Email sent successfully
    } catch (Exception $e) {
        error_log("Failed to send OTP: " . $e->getMessage()); // Log the error
        return false; // Failed to send email
    }
}

// Function to store OTP in the database
function storeOTP($email, $otp) {
    $db = new DataBase();
    if ($db->dbConnect()) {
        $expiresAt = date("Y-m-d H:i:s", strtotime("+10 minutes")); // OTP expires in 10 minutes
        $sql = "INSERT INTO password_reset_otps (email, otp, expires_at) VALUES (?, ?, ?)";
        $stmt = $db->connect->prepare($sql);
        $stmt->bind_param("sss", $email, $otp, $expiresAt);
        if ($stmt->execute()) {
            return true; // OTP stored successfully
        } else {
            return false; // Failed to store OTP
        }
    } else {
        return false; // Database connection failed
    }
}

// Function to verify OTP
function verifyOTP($email, $otp) {
    $db = new DataBase();
    if ($db->dbConnect()) {
        $currentTime = date("Y-m-d H:i:s");
        $sql = "SELECT * FROM password_reset_otps WHERE email = ? AND otp = ? AND expires_at > ?";
        $stmt = $db->connect->prepare($sql);
        $stmt->bind_param("sss", $email, $otp, $currentTime);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return true; // OTP is valid
        } else {
            return false; // OTP is invalid or expired
        }
    } else {
        return false; // Database connection failed
    }
}

// Main script logic
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (isset($data['email']) && isset($data['otp'])) {
    // Verify OTP
    if (verifyOTP($data['email'], $data['otp'])) {
        echo json_encode(["status" => "success", "message" => "OTP is valid!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "OTP is invalid or expired."]);
    }
} elseif (isset($data['email'])) {
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email address."]);
        exit;
    }

    // Generate and send OTP
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    if (storeOTP($data['email'], $otp) && sendOTP($data['email'], $otp)) {
        echo json_encode(["status" => "success", "message" => "OTP sent successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to send OTP."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email address is required."]);
}
?>