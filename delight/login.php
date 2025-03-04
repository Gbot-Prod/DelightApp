<?php
require "DataBase.php";

// Get the raw JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$db = new DataBase();
if (isset($data['username']) && isset($data['password'])) {
    if ($db->dbConnect()) {
        if ($db->logIn("users", $data['username'], $data['password'])) {
            echo "Login successful";
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Error: Database connection";
    }
} else {
    echo "All fields are required";
}
?>