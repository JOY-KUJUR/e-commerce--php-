<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../config.php';

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Prepare and execute the delete query
    try {
        $stmt = $pdo->prepare("DELETE FROM Books WHERE id = :id");
        $stmt->execute(['id' => $book_id]);

        // Redirect back to the books view page
        header("Location: view_books.php");
        exit();
    } catch (Exception $e) {
        // Handle error if needed
        $error = "Error: " . $e->getMessage();
    }
} else {
    // If no ID is provided, redirect back
    header("Location: view_books.php");
    exit();
}
