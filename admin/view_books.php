<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch books added by this admin user
require '../config.php';
$stmt = $pdo->prepare("SELECT * FROM Books WHERE user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user']['id']]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Books</title>
    <!-- Add Bootstrap for modal functionality -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <h1>Books Added by You</h1>
    <a href="add_book.php">Add New Book</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Description</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Cover Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['description']) ?></td>
                    <td><?= htmlspecialchars($book['price']) ?></td>
                    <td><?= htmlspecialchars($book['category']) ?></td>
                    <td><?= htmlspecialchars($book['stock']) ?></td>
                    <td>
                        <?php if ($book['cover_image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($book['cover_image']) ?>" alt="Book Cover" width="100">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Edit Button: Redirect to the edit page -->
                        <a href="edit_book.php?id=<?= $book['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

                        <!-- Delete Button: Trigger modal for deletion confirmation -->
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" 
                            data-book-id="<?= $book['id'] ?>" data-book-title="<?= htmlspecialchars($book['title']) ?>">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal for delete confirmation -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the book "<span id="bookTitle"></span>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a id="deleteButton" href="" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // When the delete button is clicked, set the modal book title and delete URL
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var bookId = button.data('book-id'); // Extract book ID
            var bookTitle = button.data('book-title'); // Extract book title
            var deleteUrl = 'delete_book.php?id=' + bookId; // URL to delete the book

            // Set the title in the modal
            $('#bookTitle').text(bookTitle);
            // Set the delete URL in the delete button
            $('#deleteButton').attr('href', deleteUrl);
        });
    </script>

</body>
</html>
