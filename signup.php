<?php
require_once "db.php"; // Using your MySQLi connection file

$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $errorMsg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } else {
        // Check if email exists
        $checkEmail = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        
        if ($checkEmail->num_rows > 0) {
            $errorMsg = "Email already registered.";
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertUser = $conn->prepare("
                INSERT INTO user (username, email, password) 
                VALUES (?, ?, ?)
            ");
            $insertUser->bind_param("sss", $username, $email, $hashedPassword);
            
            if ($insertUser->execute()) {
                $successMsg = "Registration successful! <a href='login.php'>Login here</a>.";
            } else {
                $errorMsg = "Registration failed. Please try again.";
            }
            
            $insertUser->close();
        }
        
        $checkEmail->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
          .btn-primary {
        background: #4863A0 !important;
        color: white !important;
        border: none !important;
        padding: 10px 20px !important;
        border-radius: 5px !important;
        cursor: pointer !important;
        width: 100% !important;
        font-size: 16px !important;
        transition: background-color 0.3s !important;
        margin: 0 !important;
        position: static !important;
        display: block !important;
    }
    .btn-primary:hover {
        background: #3b5288 !important;
    }
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-title {
            text-align: center;
            color: #4863A0;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-primary {
            background: #4863A0;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background: #3b5288;
        }
        .error-msg {
            color: #d9534f;
            text-align: center;
            margin-bottom: 1rem;
        }
        .success-msg {
            color: #5cb85c;
            text-align: center;
            margin-bottom: 1rem;
        }
        .login-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #4863A0;
            text-decoration: none;
        }
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="form-title">Create Account</h2>
        
        <?php if ($errorMsg): ?>
            <div class="error-msg"><?= $errorMsg ?></div>
        <?php endif; ?>
        
        <?php if ($successMsg): ?>
            <div class="success-msg"><?= $successMsg ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Username" required 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-primary">Sign Up</button>
        </form>

        <a href="login.php" class="login-link">Already have an account? Login</a>
    </div>
</body>
</html>