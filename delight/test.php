<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "delight";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test query
$sql = "INSERT INTO users (username, password) VALUES ('testuser', '" . password_hash('testpassword', PASSWORD_DEFAULT) . "')";
if ($conn->query($sql) === TRUE) {
    echo "Test query executed successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>