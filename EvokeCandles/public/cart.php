<?php
include '../includes/db.php';
session_start();

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
    $conn->query($sql);
}

$result = $conn->query("SELECT * FROM cart_items WHERE user_id = $user_id");
while ($item = $result->fetch_assoc()) {
    echo "Product ID: " . $item['product_id'] . " Quantity: " . $item['quantity'] . "<br>";
}
?>

