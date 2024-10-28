<?php
include '../includes/db.php';
session_start();

$user_id = $_SESSION['user_id'];
$total = 0;

$result = $conn->query("SELECT * FROM cart_items WHERE user_id = $user_id");
while ($item = $result->fetch_assoc()) {
    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    
    $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
    $total += $product['price'] * $quantity;
}

// Here, integrate payment gateway logic
echo "Total Amount: $" . $total;
?>
<form action="process_payment.php" method="POST">
    <button type="submit">Proceed to Payment</button>
</form>

