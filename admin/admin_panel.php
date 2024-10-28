<?php
session_start();
include '../includes/db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for troubleshooting (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables for dashboard data
$totalProductsSold = 0;
$totalIncome = 0.0;
$activeOrders = [];
$dispatchedOrders = [];
$liveProductsCount = 0;

// Query to get total products sold and total income
try {
    $soldQuery = "
        SELECT SUM(o.quantity) AS totalProductsSold, 
               SUM(CASE 
                       WHEN p.discount_price IS NOT NULL AND p.discount_price > 0 THEN o.quantity * p.discount_price 
                       ELSE o.quantity * p.price 
                   END) AS totalIncome 
        FROM orders o
        JOIN products p ON o.product_id = p.id 
        WHERE o.status = 'delivered'";
    
    $soldResult = $conn->query($soldQuery);
    $soldData = $soldResult->fetch_assoc();
    $totalProductsSold = $soldData['totalProductsSold'] ?? 0;
    $totalIncome = $soldData['totalIncome'] ?? 0.0;
} catch (Exception $e) {
    echo "Error retrieving sales data: " . $e->getMessage();
}

// Query to get active (yet to be dispatched) orders - showing last 5
try {
    $activeQuery = "SELECT * FROM orders WHERE status = 'active' ORDER BY order_date DESC LIMIT 5";
    $activeOrdersResult = $conn->query($activeQuery);
    while ($row = $activeOrdersResult->fetch_assoc()) {
        $activeOrders[] = $row;
    }
} catch (Exception $e) {
    echo "Error retrieving active orders: " . $e->getMessage();
}

// Query to get dispatched but not delivered orders - showing last 5
try {
    $dispatchedQuery = "SELECT * FROM orders WHERE status = 'dispatched' ORDER BY order_date DESC LIMIT 5";
    $dispatchedOrdersResult = $conn->query($dispatchedQuery);
    while ($row = $dispatchedOrdersResult->fetch_assoc()) {
        $dispatchedOrders[] = $row;
    }
} catch (Exception $e) {
    echo "Error retrieving dispatched orders: " . $e->getMessage();
}

// Query to count live products in stock
try {
    $liveProductsQuery = "SELECT COUNT(*) AS liveProductsCount FROM products WHERE inStock = 'yes'";
    $liveProductsResult = $conn->query($liveProductsQuery);
    $liveProductsData = $liveProductsResult->fetch_assoc();
    $liveProductsCount = $liveProductsData['liveProductsCount'] ?? 0;
} catch (Exception $e) {
    echo "Error retrieving live products count: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Evoke Candles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Admin Dashboard</h1>

            <div class="columns">
                <div class="column is-3">
                    <div class="box">
                        <p class="title is-5">Total Products Sold</p>
                        <p class="subtitle"><?= $totalProductsSold ?></p>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="box">
                        <p class="title is-5">Total Income</p>
                        <p class="subtitle">$<?= number_format($totalIncome, 2) ?></p>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="box">
                        <p class="title is-5">Live Products in Stock</p>
                        <p class="subtitle"><?= $liveProductsCount ?></p>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-6">
                    <div class="box">
                        <p class="title is-5">Active Orders</p>
                        <ul>
                            <?php foreach ($activeOrders as $order): ?>
                                <li>Order #<?= $order['order_id'] ?> - <?= $order['order_date'] ?></li>
                            <?php endforeach; ?>
                            <?php if (empty($activeOrders)): ?>
                                <p>No active orders</p>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="column is-6">
                    <div class="box">
                        <p class="title is-5">Dispatched Orders</p>
                        <ul>
                            <?php foreach ($dispatchedOrders as $order): ?>
                                <li>Order #<?= $order['order_id'] ?> - <?= $order['order_date'] ?></li>
                            <?php endforeach; ?>
                            <?php if (empty($dispatchedOrders)): ?>
                                <p>No dispatched orders</p>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
