<?php
// Start session at the beginning
session_start();

// Include database connection
require_once "db.php";

$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    // Sanitize inputs
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $pass = $_POST["password"]; // Don't escape password - we'll hash it
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT `user_id`, `username`, `email`, `password` FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $ro = $res->fetch_assoc();
        
        // Verify password (assuming passwords are hashed in database)
        if (password_verify($pass, $ro["password"])) {
            $_SESSION["user_id"] = $ro["user_id"];
            $_SESSION["username"] = $ro["username"];
            header("Location: dashboard.php");
            exit();
        } else {
            $errorMsg = "Invaluser_id email or password";
        }
    } else {
        $errorMsg = "No account found";
    }
    
    $stmt->close();
}

// Close connection at the end of script
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign In</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        /* Additional styles to fix the specific issues */
        .form {
            position: relative;
            padding-bottom: 40px; /* Make space for the register link */
        }
        
        .btn-primary {
            position: static; /* Remove absolute positioning */
            wuser_idth: auto; /* Let the button size naturally */
            padding: 10px 220px; /* Comfortable padding */
            display: inline-block; /* Make it inline */
            margin-top: 10px; /* Add some spacing */
            background-color: #4863A0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-primary:hover {
            background-color: #3b5288;
        }
        
        .text-center {
            text-align: center;
        }
        
        .register-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4863A0;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link:hover {
            text-decoration: underline;
            color: #3b5288;
        }
        
        #erroMsg {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .form-control {
            wuser_idth: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        
        .title {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div user_id="backgroundLogin">
        <fieldset class="form">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <?php if ($errorMsg) { ?>
                    <div user_id="erroMsg"><?php echo $errorMsg; ?></div>
                <?php } ?>
                <div class="form-group">
                    <label class="title">Email</label>
                    <input type="text" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="title">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary" name="login">Sign In</button>
                </div>
                <a href="signup.php" class="register-link">Not a member? Register now!</a>
            </form>
        </fieldset>
    </div>
</div>
</body>
</html>