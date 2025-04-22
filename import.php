<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


// File upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $allowed_types = ['pdf', 'epub', 'mobi'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validate file type
    if (!in_array($file_ext, $allowed_types)) {
        $success_message = "Only PDF, EPUB, and MOBI files allowed.";
    } 
    // Validate file size
    elseif ($_FILES['file']['size'] > 20000000) { // 20MB max
        $success_message = "File too large (max 20MB).";
    }
    // Check for upload errors
    elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $success_message = "Upload error: " . $_FILES['file']['error'];
    }
    // Proceed with upload if all validations pass
    else {
        // Use absolute path for upload directory
        $upload_dir = __DIR__ . '/uploads/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $success_message = "Failed to create upload directory.";
            }
        }
        
        // Check if directory is writable
        if (!is_writable($upload_dir)) {
            $success_message = "Upload directory is not writable.";
        } else {
            // Generate unique filename to prevent conflicts
            $new_filename = uniqid() . '_' . basename($file_name);
            $destination = $upload_dir . $new_filename;
            
            // Move the uploaded file
            if (move_uploaded_file($file_tmp, $destination)) {
                // Connect to database
                $link = mysqli_connect("localhost", "root", "", "mybooklibrary");
                if (mysqli_connect_errno()) {
                    $success_message = "Database connection failed: " . mysqli_connect_error();
                } else {
                    // Set a default user ID (replace this with your actual user authentication)
                    $user_id = 1; // CHANGE THIS TO YOUR ACTUAL USER ID SYSTEM
                    
                    // Prepare and execute SQL statement
                    $stmt = mysqli_prepare($link, "INSERT INTO books (user_id, title, file_name, upload_date) VALUES (?, ?, ?, ?)");
                    $upload_date = date('Y-m-d H:i:s');
                    mysqli_stmt_bind_param($stmt, "isss", $user_id, $file_name, $new_filename, $upload_date);

                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "File uploaded successfully!";
                    } else {
                        $success_message = "Error saving file to database: " . mysqli_error($link);
                        // Remove the uploaded file if database insert failed
                        unlink($destination);
                    }

                    mysqli_stmt_close($stmt);
                    mysqli_close($link);
                }
            } else {
                $success_message = "Error moving uploaded file. Check permissions.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Files</title>
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
            padding: 0;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
        }
        .btn-library {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #5cb85c;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-library:hover {
            background-color: #4cae4c;
            color: white;
            text-decoration: none;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 style="margin-top: 0;">Import Files</h2>
            
            <a href="books.php" class="btn-library">View My Library</a>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-<?php echo strpos($success_message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fileInput">Choose File (PDF, EPUB, MOBI)</label>
                    <input type="file" name="file" id="fileInput" class="form-control" accept=".pdf,.epub,.mobi" required>
                    <small class="form-text text-muted">Maximum file size: 20MB</small>
                </div>
                <div class="button-container">
                    <a href="homepage.php" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Import File</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>