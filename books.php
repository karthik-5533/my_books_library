<?php
session_start();

$link = mysqli_connect("localhost", "root", "", "mybooklibrary");
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Updated query to use your books table structure
$user_id = 1; 
$stmt = mysqli_prepare($link, "SELECT bk_id, title, file_name, upload_date FROM books WHERE user_id = ? ORDER BY upload_date DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Library</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, rgb(199, 118, 224), #cfdef3);
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            color: rgb(19, 2, 18);
            margin-bottom: 30px;
        }
        .book-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .book-info {
            flex-grow: 1;
        }
        .book-actions a {
            margin-left: 10px;
        }
        .btn-primary {
            background-color: #4C787E;
            border-color: #4C787E;
        }
        .btn-primary:hover {
            background-color: #006666;
            border-color: #006666;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-back {
            margin-bottom: 20px;
            display: inline-block;
        }
        .upload-date {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .no-books {
            text-align: center;
            padding: 30px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="homepage.php" class="btn btn-secondary btn-back">Back to Home</a>
        <a href="import.php" class="btn btn-primary btn-back">Import New Book</a>
        
        <h1>My Library</h1>
        
        <?php if (empty($books)): ?>
            <div class="no-books">
                <p>You haven't uploaded any books yet.</p>
                <a href="import.php" class="btn btn-primary">Import Your First Book</a>
            </div>
        <?php else: ?>
            <div class="book-list">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-info">
                            <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                            <div class="upload-date">
                                Uploaded on: <?php echo date('F j, Y, g:i a', strtotime($book['upload_date'])); ?>
                            </div>
                        </div>
                        <div class="book-actions">
                            <a href="delete_book.php?id=<?php echo $book['bk_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
