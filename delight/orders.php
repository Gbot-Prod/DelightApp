<?php
require "DataBase.php";

// Initialize the DataBase class
$database = new DataBase();

// Connect to the database
$conn = $database->dbConnect();

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the raw POST data
$data = file_get_contents("php://input");

// Decode the JSON data
$jsonData = json_decode($data, true);

if ($jsonData) {
    // Retrieve data from the JSON object
    $id = $jsonData["id"];
    $product_name = $jsonData["product_name"];
    $product_price = $jsonData["product_price"];
    $quantity = $jsonData["quantity"];
    $total_price = $product_price * $quantity;

    // Log the received data
    error_log("Received data: id=$id, product_name=$product_name, product_price=$product_price, quantity=$quantity, total_price=$total_price");

    // Prepare the SQL query
    $sql = "INSERT INTO orders (user_id, product_name, product_price, quantity, total_price, order_date, status)
            VALUES ('$id', '$product_name', '$product_price', '$quantity', '$total_price', NOW(), 'Pending')";

    // Log the SQL query
    error_log("Executing query: $sql");

    // Execute the query
    if (mysqli_query($conn, $sql)) {
        echo "Order placed successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    echo "Invalid JSON data!";
}

// Close the database connection
mysqli_close($conn);
?>