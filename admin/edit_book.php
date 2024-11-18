<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch the book details if the book ID is passed in the URL
require '../config.php';

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Fetch the book details
    $stmt = $pdo->prepare("SELECT * FROM Books WHERE id = :id");
    $stmt->execute(['id' => $book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        die("Book not found!");
    }
}

// Handle form submission to update the book
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    $cover_image = $book['cover_image']; // Default to current cover image if not changed

    // Handle file upload for cover image (if changed)
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $image_name = $_FILES['cover_image']['name'];
        $image_tmp_name = $_FILES['cover_image']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Sanitize the book title for the image filename
        $sanitized_title = preg_replace('/[^a-zA-Z0-9-_]/', '_', strtolower($title));

        // Combine the sanitized title with the image extension
        $cover_image = $sanitized_title . '.' . $image_ext; // Use the sanitized title as the image name
        $upload_dir = '../uploads/'; // Ensure this directory exists

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($image_tmp_name, $upload_dir . $cover_image)) {
            echo "Cover image uploaded successfully.<br>";
        } else {
            $error = "Error uploading cover image.";
        }
    }

    // Update book details in the database
    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE Books SET title = :title, author = :author, description = :description, 
                                   price = :price, category = :category, stock = :stock, cover_image = :cover_image 
                                   WHERE id = :id");
            $stmt->execute([
                'title' => $title,
                'author' => $author,
                'description' => $description,
                'price' => $price,
                'category' => $category,
                'stock' => $stock,
                'cover_image' => $cover_image,
                'id' => $book_id
            ]);
            header("Location: view_books.php");
            exit();
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
</head>
<body>
    <h1>Edit Book</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required><br>

        <label>Author:</label>
        <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" required><br>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($book['description']) ?></textarea><br>

        <label>Price:</label>
        <input type="number" name="price" value="<?= htmlspecialchars($book['price']) ?>" step="0.01" required><br>

        <label>Category:</label>
        <input type="text" name="category" value="<?= htmlspecialchars($book['category']) ?>" required><br>

        <label>Stock Quantity:</label>
        <input type="number" name="stock" value="<?= htmlspecialchars($book['stock']) ?>" required><br>

        <label>Cover Image:</label>
        <input type="file" name="cover_image" accept="image/*"><br>
        <small>Current image: <img src="../uploads/<?= htmlspecialchars($book['cover_image']) ?>" alt="Cover Image" width="100"></small><br>

        <button type="submit">Update Book</button>
    </form>
    <br>
    <a href="view_books.php">Back to Books List</a>
</body>
</html>
