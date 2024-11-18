<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch all available books
require 'config.php';
$stmt = $pdo->prepare("SELECT * FROM Books");
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle adding books to the cart
if (isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];

    // Set quantity to 1 by default
    $quantity = 1;

    // Fetch the book's stock level
    $stmt = $pdo->prepare("SELECT stock FROM Books WHERE id = :id");
    $stmt->execute(['id' => $book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the book is in stock
    if ($book['stock'] <= 0) {
        $_SESSION['error'] = "This book is out of stock.";
    } else {
        // Add to cart logic
        if (!isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id] = [
                'quantity' => $quantity
            ];
        } else {
            $_SESSION['cart'][$book_id]['quantity'] += $quantity;
        }
        $_SESSION['success'] = "Book added to the cart!";
    }
    header("Location: shop.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Bookstore</title>
</head>
<body>
    <h1>Welcome to the Bookstore</h1>
    
    <!-- Cart Link -->
    <a href="view_cart.php">View Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?= $_SESSION['success'] ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <h2>Available Books</h2>
    <div class="books">
        <?php foreach ($books as $book): ?>
            <div class="book">
                <img src="uploads/<?= htmlspecialchars($book['cover_image']) ?>" alt="Book Cover" width="100">
                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p>Author: <?= htmlspecialchars($book['author']) ?></p>
                <p>Price: $<?= htmlspecialchars($book['price']) ?></p>
                <p><?= htmlspecialchars($book['description']) ?></p>
                
                <!-- Check Stock -->
                <?php if ($book['stock'] <= 0): ?>
                    <p style="color: red;">Out of Stock</p>
                <?php else: ?>
                    <!-- Add to Cart Button -->
                    <form method="POST">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
