<?php
session_start();
require '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    header("Location: view_orders.php");
    exit();
}

$orderId = $_GET['order_id'];

// Fetch the order details, including address from delivery_locations
$stmt = $pdo->prepare("
    SELECT 
        orders.*, 
        order_dispatch.dispatch_status, 
        order_dispatch.transaction_id, 
        users.name AS user_name, 
        users.email AS user_email,
        delivery_locations.id AS address_id,
        delivery_locations.address_line1, 
        delivery_locations.address_line2, 
        delivery_locations.city, 
        delivery_locations.state, 
        delivery_locations.zip_code 
    FROM orders
    LEFT JOIN order_dispatch ON orders.id = order_dispatch.order_id
    JOIN users ON orders.user_id = users.id
    JOIN delivery_locations ON users.id = delivery_locations.user_id
    WHERE orders.id = :order_id
");
$stmt->execute(['order_id' => $orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found.";
    exit();
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT order_items.*, books.title 
    FROM order_items 
    JOIN books ON order_items.book_id = books.id 
    WHERE order_items.order_id = :order_id
");
$stmt->execute(['order_id' => $orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['dispatch_status']) || isset($_POST['transaction_id'])) {
        // Update dispatch status and transaction ID
        $newStatus = $_POST['dispatch_status'];
        $transactionId = $_POST['transaction_id'];
 // Update order status
 $stmt = $pdo->prepare("
 UPDATE orders 
 SET status = :status 
 WHERE id = :order_id
");
$stmt->execute([
 'status' =>'Completed',
 'order_id' => $orderId
]);
        $stmt = $pdo->prepare("
            UPDATE order_dispatch 
            SET dispatch_status = :dispatch_status, transaction_id = :transaction_id 
            WHERE order_id = :order_id
        ");
        $stmt->execute([
            'dispatch_status' => $newStatus,
            'transaction_id' => $transactionId,
            'order_id' => $orderId
        ]);

        // Update shipping address if it is modified
        $stmt = $pdo->prepare("
            UPDATE delivery_locations 
            SET address_line1 = :address_line1, 
                address_line2 = :address_line2, 
                city = :city, 
                state = :state, 
                zip_code = :zip_code 
            WHERE id = :address_id
        ");
        $stmt->execute([
            'address_line1' => $_POST['address_line1'],
            'address_line2' => $_POST['address_line2'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'zip_code' => $_POST['zip_code'],
            'address_id' => $order['address_id']
        ]);

        header("Location: view_orders.php");
        exit();
    } elseif (isset($_POST['cancel_order'])) {
        // Cancel the order
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = :order_id");
        $stmt->execute(['order_id' => $orderId]);

        header("Location: view_orders.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Order Details</h1>
    <h3>Order ID: <?= htmlspecialchars($order['id']) ?></h3>
    <p>User: <?= htmlspecialchars($order['user_name']) ?> (<?= htmlspecialchars($order['user_email']) ?>)</p>
    <p>Total Price: $<?= number_format($order['total_price'], 2) ?></p>
    <p>Status: <?= htmlspecialchars($order['status']) ?></p>
    <p>Dispatch Status: <?= htmlspecialchars($order['dispatch_status'] ?? 'Not Assigned') ?></p>
    <p>Transaction ID: <?= htmlspecialchars($order['transaction_id'] ?? 'None') ?></p>
    <p>Order Date: <?= htmlspecialchars($order['created_at']) ?></p>

    <h2>Shipping Address</h2>
    <p><strong>Address Line 1:</strong> <?= htmlspecialchars($order['address_line1']) ?></p>
    <p><strong>Address Line 2:</strong> <?= htmlspecialchars($order['address_line2'] ?? 'N/A') ?></p>
    <p><strong>City:</strong> <?= htmlspecialchars($order['city']) ?></p>
    <p><strong>State:</strong> <?= htmlspecialchars($order['state']) ?></p>
    <p><strong>Zip Code:</strong> <?= htmlspecialchars($order['zip_code']) ?></p>

    <h2>Items in Order</h2>
    <table>
        <tr>
            <th>Book Title</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars($item['quantity']) ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Update Order Status</h2>
    <form method="POST">
        <label for="dispatch_status">Dispatch Status:</label>
        <select name="dispatch_status" id="dispatch_status" required>
            <option value="Pending" <?= $order['dispatch_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Dispatched" <?= $order['dispatch_status'] === 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
            <option value="Delivered" <?= $order['dispatch_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        </select>
        <br><br>
        <label for="transaction_id">Transaction ID:</label>
        <input type="text" name="transaction_id" id="transaction_id" value="<?= htmlspecialchars($order['transaction_id'] ?? '') ?>">
        <br><br>
        <button type="submit">Update Status</button>
    </form>

    <h2>Cancel Order</h2>
    <form method="POST">
        <button type="submit" name="cancel_order">Cancel Order</button>
    </form>
</body>
</html>

