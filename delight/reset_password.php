<?php
require "DataBase.php"; // Include your database connection file

header('Content-Type: application/json'); // Set response header to JSON

// Function to reset password
function resetPassword($email, $newPassword) {
    $db = new DataBase();
    if ($db->dbConnect()) {
        error_log("Database connection successful.");

        // Check if the email exists
        $checkEmailSql = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $db->connect->prepare($checkEmailSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            error_log("Hashed password: " . $hashedPassword);

            // Update the password in the database
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $db->connect->prepare($sql);
            $stmt->bind_param("ss", $hashedPassword, $email);
            if ($stmt->execute()) {
                error_log("Password updated successfully.");
                return true; // Password reset successful
            } else {
                error_log("Failed to execute SQL statement: " . $stmt->error);
                return false; // Failed to reset password
            }
        } else {
            error_log("Email does not exist in the database.");
            return false; // Email not found
        }
    } else {
        error_log("Database connection failed.");
        return false; // Database connection failed
    }
}

// Main script logic
$input = file_get_contents("php://input");
$data = json_decode($input, true);

error_log("Received data: " . print_r($data, true)); // Log the received data

if (isset($data['email']) && isset($data['newPassword']) && isset($data['confirmPassword'])) {
    // Validate inputs
    if ($data['newPassword'] !== $data['confirmPassword']) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
        exit;
    }

    // Reset password
    if (resetPassword($data['email'], $data['newPassword'])) {
        echo json_encode(["status" => "success", "message" => "Password reset successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to reset password."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
}
?>