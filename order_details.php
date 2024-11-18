<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: my_orders.php"); // Redirect if no order ID is provided
    exit();
}

$orderId = $_GET['order_id'];
$userId = $_SESSION['user']['id'];

// Fetch order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id");
$stmt->execute(['order_id' => $orderId, 'user_id' => $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if the order doesn't belong to the user
if (!$order) {
    header("Location: my_orders.php");
    exit();
}

// Fetch items in the order
$stmt = $pdo->prepare("
    SELECT oi.*, b.title, b.author 
    FROM order_items oi 
    JOIN books b ON oi.book_id = b.id 
    WHERE oi.order_id = :order_id
");
$stmt->execute(['order_id' => $orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch delivery status
$stmt = $pdo->prepare("SELECT * FROM order_dispatch WHERE order_id = :order_id");
$stmt->execute(['order_id' => $orderId]);
$dispatchDetails = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
</head>
<body>
    <h1>Order Details</h1>

    <h2>Order Information</h2>
    <p>Order ID: <?= $order['id'] ?></p>
    <p>Total Price: $<?= number_format($order['total_price'], 2) ?></p>
    <p>Status: <?= htmlspecialchars($order['status']) ?></p>
    <p>Date: <?= $order['created_at'] ?></p>

    <h2>Delivery Status</h2>
    <p>Status: <?= htmlspecialchars($dispatchDetails['dispatch_status']) ?></p>

    <h2>Items Ordered</h2>
    <table>
        <tr>
            <th>Book</th>
            <th>Author</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars($item['author']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Total: $<?= number_format($order['total_price'], 2) ?></h3>

    <a href="my_orders.php">Back to Orders</a>
</body>
</html>
