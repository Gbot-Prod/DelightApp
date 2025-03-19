<?php
require "DataBase.php";

$database = new DataBase();
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$db = new DataBase();
if (isset($data['username']) && isset($data['password'])) {
    if ($db->dbConnect()) {
        if ($db->logIn("users", $data['username'], $data['password'])) {
            // Login successful
            echo json_encode(["status" => "success", "message" => "Login successful"]);
        } else {
            // Invalid credentials
            echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
        }
    } else {
        // Database connection error
        echo json_encode(["status" => "error", "message" => "Error: Database connection"]);
    }
} else {
    // Missing fields
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
}
?>