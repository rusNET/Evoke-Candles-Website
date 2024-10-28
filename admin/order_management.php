<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT o.id, u.username, o.total_amount, o.status FROM orders o JOIN users u ON o.user_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management - Evoke Candles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.4/css/bulma.min.css">
</head>
<body>
    <section class="hero is-warning">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">Order Management</h1>
                <a class="button is-danger" href="logout.php">Logout</a>
            </div>
        </div>
    </section>
    <div class="container">
        <h2 class="subtitle">Orders</h2>
        <table class="table is-fullwidth is-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo $order['username']; ?></td>
                        <td><?php echo $order['total_amount']; ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td>
                            <a class="button is-info is-small" href="view_order.php?id=<?php echo $order['id']; ?>">View</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

