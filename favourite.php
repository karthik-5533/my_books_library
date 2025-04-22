<?php
require 'db.php';
$user_id = 1; // In a real app, you'd get this from the session
// Ensure no output before session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle AJAX favorite toggle (for consistency with book.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_toggle_favorite'])) {
    $response = ['success' => false];
    $bk_id = (int)$_POST['bk_id'];
    
    // Check if book exists
    $check_book = $conn->prepare("SELECT bk_id FROM books WHERE bk_id = ?");
    $check_book->bind_param("i", $bk_id);
    $check_book->execute();
    $check_book->store_result();
    
    if ($check_book->num_rows > 0) {
        // Check if already favorited
        $check_fav = $conn->prepare("SELECT id FROM favorites WHERE book_id = ? AND user_id = ?");
        $check_fav->bind_param("ii", $bk_id, $user_id);
        $check_fav->execute();
        $check_fav->store_result();

        if ($check_fav->num_rows > 0) {
            // Remove from favorites
            $remove_fav = $conn->prepare("DELETE FROM favorites WHERE book_id = ? AND user_id = ?");
            if ($remove_fav->bind_param("ii", $bk_id, $user_id) && $remove_fav->execute()) {
                $response = ['success' => true, 'is_favorite' => false];
            }
        } else {
            // Add to favorites
            $add_fav = $conn->prepare("INSERT INTO favorites (book_id, user_id, created_at) VALUES (?, ?, NOW())");
            if ($add_fav->bind_param("ii", $bk_id, $user_id) && $add_fav->execute()) {
                $response = ['success' => true, 'is_favorite' => true];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle removing from favorites via form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $book_id = (int)$_POST['bk_id'];
    
    $stmt = $conn->prepare("DELETE FROM favorites WHERE book_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $book_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: favorites.php");
    exit();
}

// Handle deleting a book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
    $book_id = (int)$_POST['bk_id'];
    
    // First remove from favorites
    $stmt = $conn->prepare("DELETE FROM favorites WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->close();
    
    // Then delete the book
    $stmt = $conn->prepare("DELETE FROM books WHERE bk_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: favorites.php");
    exit();
}

// Initialize array to store favorite books
$favorite_books = [];

// Fetch favorite books with their details
$stmt = $conn->prepare("
    SELECT b.*, f.id as favorite_id 
    FROM books b
    JOIN favorites f ON b.bk_id = f.book_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $favorite_books[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorite Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .container {
            max-width: 1800px;
            padding: 20px;
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
        }
        
        @media (max-width: 1400px) {
            .books-grid {
                grid-template-columns: repeat(3, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 992px) {
            .books-grid {
                grid-template-columns: repeat(2, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 576px) {
            .books-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .book-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .book-preview {
            height: 200px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .book-icon {
            font-size: 3.5rem;
            color: #dc3545;
        }
        
        .book-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3em;
            font-size: 1.1rem;
        }
        
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50% !important;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-footer {
            padding: 0.75rem !important;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-heart-fill text-danger"></i> My Favorite Books</h2>
            <a href="book.php" class="btn btn-outline-primary">
                <i class="bi bi-book"></i> Back to Library
            </a>
        </div>
        
        <?php if (!empty($favorite_books)): ?>
            <div class="books-grid">
                <?php foreach ($favorite_books as $book): ?>
                    <div class="book-card">
                        <div class="book-preview">
                            <?php if ($book['file_type'] === 'application/pdf'): ?>
                                <i class="bi bi-file-earmark-pdf book-icon"></i>
                            <?php elseif ($book['file_type'] === 'application/epub+zip'): ?>
                                <i class="bi bi-journal-bookmark book-icon"></i>
                            <?php else: ?>
                                <i class="bi bi-book book-icon"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-3">
                            <h5 class="card-title book-title" title="<?= htmlspecialchars($book['title']) ?>">
                                <?= htmlspecialchars($book['title']) ?>
                            </h5>
                            <p class="card-text text-muted small mb-2">
                                <i class="bi bi-calendar"></i> 
                                <?= date('M d, Y', strtotime($book['upload_date'])) ?>
                            </p>
                            <p class="card-text text-muted small">
                                <i class="bi bi-tag"></i>
                                <?= ucfirst($book['category']) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <!-- View Button -->
                                <a href="view_pdf.php?bk_id=<?= $book['bk_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary action-btn"
                                   title="View Book">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <!-- Remove from Favorites Button -->
                                <button type="button" 
                                       class="btn btn-sm btn-outline-danger action-btn toggle-favorite"
                                       data-bk-id="<?= $book['bk_id'] ?>"
                                       title="Remove from favorites">
                                    <i class="bi bi-heart-fill text-danger"></i>
                                </button>
                                
                                <!-- Delete Button -->
                                <form method="post" class="d-inline delete-book-form">
                                    <input type="hidden" name="bk_id" value="<?= $book['bk_id'] ?>">
                                    <button type="submit" 
                                            name="delete_book" 
                                            class="btn btn-sm btn-outline-secondary action-btn"
                                            title="Delete Book">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                
                                <!-- Download Button -->
                                <a href="download.php?id=<?= $book['bk_id'] ?>" 
                                   class="btn btn-sm btn-outline-success action-btn"
                                   title="Download Book">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-heart" style="font-size: 2rem;"></i>
                <h4 class="mt-3">No Favorite Books Yet</h4>
                <p>You haven't added any books to your favorites. Click the heart icon on books to add them here.</p>
                <a href="book.php" class="btn btn-primary mt-2">
                    <i class="bi bi-book"></i> Browse Books
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Handle favorite toggling via AJAX
            document.querySelectorAll('.toggle-favorite').forEach(button => {
                button.addEventListener('click', function() {
                    const bkId = this.getAttribute('data-bk-id');
                    
                    fetch('book.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `ajax_toggle_favorite=1&bk_id=${bkId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && !data.is_favorite) {
                            // If removed from favorites, remove the card from the favorites page
                            const bookCard = this.closest('.book-card');
                            bookCard.style.opacity = '0';
                            setTimeout(() => {
                                bookCard.remove();
                                
                                // Check if we need to show the "No favorites" message
                                const remainingCards = document.querySelectorAll('.book-card');
                                if (remainingCards.length === 0) {
                                    location.reload(); // Reload to show "no favorites" message
                                }
                            }, 300);
                        }
                    });
                });
            });
            
            // Add confirmation for delete book action
            document.querySelectorAll('.delete-book-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>