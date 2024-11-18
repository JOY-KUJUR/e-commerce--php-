<?php
session_start();
// echo '<pre>'; print_r($_SESSION); echo '</pre>'; // Debug: Print session data

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo 'Redirecting to index.php...'; // Debug: Check why it's redirecting
    header("Location: ../index.php");
    exit();
}

echo "<h1>Welcome, Admin " . htmlspecialchars($_SESSION['user']['name']) . "!</h1>";
echo '<a href="../logout.php">Logout</a>';

include('admin_header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
   

<a href="view_books.php">view book</a>
</body>
</html>

