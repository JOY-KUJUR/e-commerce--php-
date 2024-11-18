<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get user ID
$userId = $_SESSION['user']['id'];

// Fetch the delivery details for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM delivery_locations WHERE user_id = :user_id AND status = 'Complete'");
$stmt->execute(['user_id' => $userId]);
$deliveryDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user has products in the cart
if (empty($_SESSION['cart'])) {
    header("Location: shop.php"); // Redirect to shop if cart is empty
    exit();
}

// Fetch cart items details
$cart = $_SESSION['cart'];
$placeholders = implode(',', array_fill(0, count($cart), '?'));
$stmt = $pdo->prepare("SELECT * FROM books WHERE id IN ($placeholders)");
$stmt->execute(array_keys($cart));
$cartBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total price
$totalPrice = 0;
foreach ($cartBooks as $book) {
    $totalPrice += $book['price'] * $cart[$book['id']]['quantity'];
}

// Process the checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert order into the orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status, created_at) VALUES (:user_id, :total_price, 'Pending', NOW())");
        $stmt->execute(['user_id' => $userId, 'total_price' => $totalPrice]);

        // Get the last inserted order ID
        $orderId = $pdo->lastInsertId();

        // Insert order details into the order_items table
        foreach ($cartBooks as $book) {
            $quantity = $cart[$book['id']]['quantity'];
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (:order_id, :book_id, :quantity, :price)");
            $stmt->execute([
                'order_id' => $orderId,
                'book_id' => $book['id'],
                'quantity' => $quantity,
                'price' => $book['price']
            ]);

            // Update the stock of the book
            $stmt = $pdo->prepare("UPDATE books SET stock = stock - :quantity WHERE id = :book_id AND stock >= :quantity");
            $stmt->execute([
                'quantity' => $quantity,
                'book_id' => $book['id']
            ]);

            // Check if stock update was successful
            if ($stmt->rowCount() === 0) {
                throw new Exception("Insufficient stock for book: " . htmlspecialchars($book['title']));
            }
        }

        // Add dispatch entry
        $stmt = $pdo->prepare("INSERT INTO order_dispatch (order_id, dispatch_status) VALUES (:order_id, 'Pending')");
        $stmt->execute(['order_id' => $orderId]);

        // Commit transaction
        $pdo->commit();

        // Clear the cart after the order
        unset($_SESSION['cart']);

        // Redirect to My Orders page
        header("Location: my_orders.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on failure
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>
    <h1>Checkout</h1>

    <!-- Display delivery details -->
    <h2>Delivery Information</h2>
    <?php if ($deliveryDetails): ?>
        <p>Address: <?= htmlspecialchars($deliveryDetails['address_line1']) ?>, <?= htmlspecialchars($deliveryDetails['address_line2']) ?></p>
        <p>City: <?= htmlspecialchars($deliveryDetails['city']) ?>, State: <?= htmlspecialchars($deliveryDetails['state']) ?></p>
        <p>Zip Code: <?= htmlspecialchars($deliveryDetails['zip_code']) ?>, Country: <?= htmlspecialchars($deliveryDetails['country']) ?></p>
    <?php else: ?>
        <p>Your delivery information is incomplete. <a href="fill_details.php">Fill Delivery Details</a></p>
    <?php endif; ?>

    <!-- Display Cart Items -->
    <h2>Your Cart</h2>
    <table>
        <tr>
            <th>Book</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($cartBooks as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td>$<?= number_format($book['price'], 2) ?></td>
                <td><?= $cart[$book['id']]['quantity'] ?></td>
                <td>$<?= number_format($book['price'] * $cart[$book['id']]['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Display total price -->
    <h3>Total: $<?= number_format($totalPrice, 2) ?></h3>

    <!-- Proceed to Checkout Button -->
    <form method="POST">
        <button type="submit">Place Order</button>
    </form>
</body>
</html>
