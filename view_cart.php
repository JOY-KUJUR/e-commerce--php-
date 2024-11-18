<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch all available books in the cart
require 'config.php';

$cartBooks = [];
$totalPrice = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Fetch books details from the database
    $bookIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM Books WHERE id IN ($placeholders)");
    $stmt->execute($bookIds);
    $cartBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total price
    foreach ($cartBooks as $book) {
        $totalPrice += $book['price'] * $_SESSION['cart'][$book['id']]['quantity'];
    }
}

// Handle updating the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $errors = [];
    foreach ($_POST['quantity'] as $bookId => $quantity) {
        // Fetch the stock of the book
        $stmt = $pdo->prepare("SELECT stock FROM Books WHERE id = :id");
        $stmt->execute(['id' => $bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($quantity > $book['stock']) {
            $errors[] = "Not enough stock for " . htmlspecialchars($book['title']);
        } else {
            $_SESSION['cart'][$bookId]['quantity'] = $quantity;
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    } else {
        header("Location: view_cart.php");
        exit();
    }
}
// Get user ID
$userId = $_SESSION['user']['id'];

// Check if user has filled the delivery details
$stmt = $pdo->prepare("SELECT * FROM delivery_locations WHERE user_id = :user_id AND status = 'Complete'");
$stmt->execute(['user_id' => $userId]);
$deliveryDetails = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Your Cart</h1>

        <!-- Error Messages -->
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Cart Table -->
        <?php if (!empty($cartBooks)): ?>
            <form method="POST">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartBooks as $book): ?>
                            <tr>
                                <td><?= htmlspecialchars($book['title']) ?></td>
                                <td>$<?= number_format($book['price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantity[<?= $book['id'] ?>]" 
                                           value="<?= $_SESSION['cart'][$book['id']]['quantity'] ?>" 
                                           min="1" max="<?= $book['stock'] ?>" class="form-control">
                                </td>
                                <td>$<?= number_format($book['price'] * $_SESSION['cart'][$book['id']]['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">
                    <h3>Total: $<?= number_format($totalPrice, 2) ?></h3>
                    <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                </div>
            </form>
        <?php else: ?>
            <p>Your cart is empty. <a href="shop.php">Continue Shopping</a></p>
        <?php endif; ?>

        <?php if ($deliveryDetails): ?>
        <!-- If delivery details are complete, show proceed to checkout button -->
        <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
    <?php else: ?>
        <!-- If delivery details are incomplete, show button to fill details -->
        <a href="fill_details.php" class="btn btn-fill-details">Fill Delivery Details</a>
    <?php endif; ?>
    </div>

    <!-- Bootstrap Modal for Out of Stock -->
    <div class="modal fade" id="outOfStockModal" tabindex="-1" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="outOfStockModalLabel">Out of Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Some items in your cart are out of stock or have insufficient quantity. Please adjust your cart.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Show modal if there are out of stock errors
    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        var myModal = new bootstrap.Modal(document.getElementById('outOfStockModal'), {
            keyboard: false
        });
        myModal.show();
    <?php endif; ?>
</script>
</html>
