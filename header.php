<?php
session_start(); // Start the session to access the session variables
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore</title>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="about.php">About Us</a></li>
                
                <!-- Display Cart Link -->
                <li><a href="view_cart.php">View Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a></li>

                <?php if (isset($_SESSION['user'])): ?>
                    <!-- If user is logged in, show Welcome and Logout -->
                    <li><a href="profile.php">Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></a></li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <!-- Admin Links -->
                        <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- If user is not logged in, show Login and Sign Up links -->
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
</body>
</html>
