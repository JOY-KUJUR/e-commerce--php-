<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];

// Fetch the user's orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
</head>
<body>
    <h1>My Orders</h1>

    <?php if (empty($orders)): ?>
        <p>You have no orders yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($orders as $order): ?>
                <li>
                    Order ID: <?= $order['id'] ?> | Total: $<?= number_format($order['total_price'], 2) ?> | Status: <?= $order['status'] ?> | Date: <?= $order['created_at'] ?>
                    <a href="order_details.php?order_id=<?= $order['id'] ?>">View Details</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
