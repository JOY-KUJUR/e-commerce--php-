<?php
session_start();
require '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch all orders along with dispatch status
$stmt = $pdo->prepare("
    SELECT 
        orders.*, 
        users.name as user_name, 
        order_dispatch.dispatch_status 
    FROM orders 
    JOIN users ON orders.user_id = users.id
    LEFT JOIN order_dispatch ON orders.id = order_dispatch.order_id 
    ORDER BY orders.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>All Orders</h1>
    <table>
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Total Price</th>
            <th>Status</th>
            <th>Dispatch Status</th>
            <th>Order Date</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['id']) ?></td>
                <td><?= htmlspecialchars($order['user_name']) ?></td>
                <td>$<?= number_format($order['total_price'], 2) ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td><?= htmlspecialchars($order['dispatch_status'] ?? 'Not Assigned') ?></td> <!-- Handle NULL values -->
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td>
                    <a href="update_order.php?order_id=<?= $order['id'] ?>">Update Status</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
