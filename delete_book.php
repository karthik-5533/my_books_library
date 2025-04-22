<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to perform this action");
}

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid book ID");
}

$book_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Connect to database
$link = mysqli_connect("localhost", "root", "", "mybooklibrary");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Start transaction
mysqli_begin_transaction($link);

try {
    // 1. Move book to trash
    $stmt = mysqli_prepare($link, "INSERT INTO trash (book_id) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    
    // 2. Delete related data (annotations, favorites, reading goals, progress)
    $tables = ['annotations', 'favorites', 'reading_goals', 'reading_progress'];
    foreach ($tables as $table) {
        $query = "DELETE FROM $table WHERE book_id = ?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $book_id);
        mysqli_stmt_execute($stmt);
    }
    
    // 3. Finally delete the book
    $stmt = mysqli_prepare($link, "DELETE FROM books WHERE bk_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $book_id, $user_id);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_affected_rows($link) === 0) {
        throw new Exception("Book not found or you don't have permission to delete it.");
    }
    
    // Commit transaction
    mysqli_commit($link);
    
    // Redirect back to library with success message
    $_SESSION['message'] = "Book deleted successfully";
    header("Location: books.php");
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($link);
    die($e->getMessage());
} finally {
    mysqli_close($link);
}
?>