<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID
$userId = $_SESSION['user']['id'];

// Check if the user has already filled delivery details
$stmt = $pdo->prepare("SELECT * FROM delivery_locations WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$deliveryDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// If delivery details exist, we will allow the user to update them
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addressLine1 = $_POST['address_line1'];
    $addressLine2 = $_POST['address_line2'] ?? null;
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zipCode = $_POST['zip_code'];
    $country = $_POST['country'];

    // Insert or update the delivery location
    if ($deliveryDetails) {
        // Update existing delivery details
        $stmt = $pdo->prepare("UPDATE delivery_locations SET 
            address_line1 = :address_line1,
            address_line2 = :address_line2,
            city = :city,
            state = :state,
            zip_code = :zip_code,
            country = :country,
            status = 'Complete'
            WHERE user_id = :user_id");
        $stmt->execute([
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
            'country' => $country,
            'user_id' => $userId
        ]);
    } else {
        // Insert new delivery details
        $stmt = $pdo->prepare("INSERT INTO delivery_locations (user_id, address_line1, address_line2, city, state, zip_code, country, status) 
            VALUES (:user_id, :address_line1, :address_line2, :city, :state, :zip_code, :country, 'Complete')");
        $stmt->execute([
            'user_id' => $userId,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
            'country' => $country
        ]);
    }

    // Redirect back to the cart page to proceed with checkout
    header("Location: view_cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fill Delivery Details</title>
</head>
<body>
    <h1>Fill Delivery Details</h1>

    <form method="POST">
        <label for="address_line1">Address Line 1:</label>
        <input type="text" name="address_line1" required><br>

        <label for="address_line2">Address Line 2 (optional):</label>
        <input type="text" name="address_line2"><br>

        <label for="city">City:</label>
        <input type="text" name="city" required><br>

        <label for="state">State:</label>
        <input type="text" name="state" required><br>

        <label for="zip_code">Zip Code:</label>
        <input type="text" name="zip_code" required><br>

        <label for="country">Country:</label>
        <input type="text" name="country" required><br>

        <button type="submit">Save Delivery Details</button>
    </form>
</body>
</html>
