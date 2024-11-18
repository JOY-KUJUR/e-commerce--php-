<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Handle form submission to save delivery details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Save the delivery details in the session
    $_SESSION['delivery_details'] = [
        'address' => $_POST['address'],
        'phone' => $_POST['phone']
    ];

    // Redirect back to the checkout page
    header("Location: checkout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Delivery Details</title>
</head>
<body>
    <h1>Enter Your Delivery Details</h1>
    <form method="POST">
        <label>Address:</label>
        <input type="text" name="address" required><br>

        <label>Phone Number:</label>
        <input type="text" name="phone" required><br>

        <button type="submit">Save Delivery Details</button>
    </form>
</body>
</html>
