<?php
include '../includes/db.php';
session_start();

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $conn->query("INSERT INTO wishlists (user_id, product_id) VALUES ($user_id, $product_id)");
}

$result = $conn->query("SELECT * FROM wishlists WHERE user_id = $user_id");
while ($wishlist = $result->fetch_assoc()) {
    echo "Product ID: " . $wishlist['product_id'] . "<br>";
}
?>

