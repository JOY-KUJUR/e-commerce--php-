<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: white;
            padding: 15px;
        }
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header .logo {
            font-size: 1.5em;
        }
        header nav {
            display: flex;
            gap: 20px;
        }
        header nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        header nav a:hover {
            background-color: #555;
        }
        .logout {
            background-color: red;
        }
        .logout:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">Admin Panel</div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="view_books.php">View Books</a>
                <a href="view_orders.php">View Orders</a>
                <a href="add_book.php">Add New Book</a>
                <a href="logout.php" class="logout">Logout</a>
            </nav> 
        </div>
    </header>

    <!-- Optional: Show the admin's name if logged in -->
    <?php if (isset($_SESSION['admin'])): ?>
        <p>Welcome, <?= htmlspecialchars($_SESSION['admin']['name']) ?>!</p>
    <?php endif; ?>
</body>
</html>
