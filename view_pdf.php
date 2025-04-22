<?php
require 'db.php';
session_start();

// 1. Check if user is logged in (replace with your actual session check)
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    die("You must be logged in to view this content");
}

// 2. Validate book ID parameter
if (!isset($_GET['bk_id']) || !is_numeric($_GET['bk_id'])) {
    header("HTTP/1.1 400 Bad Request");
    die("Invalid book ID");
}

$book_id = (int)$_GET['bk_id'];
$user_id = (int)$_SESSION['user_id'];

// 3. Fetch book data with user verification
$stmt = $conn->prepare("SELECT file_data, title, file_type FROM books WHERE bk_id = ? AND user_id = ?");
if (!$stmt) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Database error: " . $conn->error);
}

$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    header("HTTP/1.1 404 Not Found");
    die("Book not found or you don't have permission to access it");
}

$stmt->bind_result($file_data, $title, $file_type);
$stmt->fetch();

// 4. Validate PDF data
if (empty($file_data) || substr($file_data, 0, 4) !== '%PDF') {
    // Attempt fallback to file system if available
    $fallback = $conn->query("SELECT file_name FROM books WHERE bk_id = $book_id");
    if ($fallback && $row = $fallback->fetch_assoc()) {
        $file_path = 'uploads/' . $row['file_name'];
        if (file_exists($file_path)) {
            $file_data = file_get_contents($file_path);
        }
    }
    
    // Final validation
    if (empty($file_data) || substr($file_data, 0, 4) !== '%PDF') {
        header("HTTP/1.1 500 Internal Server Error");
        die("The PDF content is corrupted. Please re-upload this book.");
    }
}

// 5. Output PDF with proper headers
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . htmlspecialchars($title) . '.pdf"');
header('Content-Length: ' . strlen($file_data));
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// 6. Output the file data
echo $file_data;

// Close connections
$stmt->close();
$conn->close();
exit();
?>