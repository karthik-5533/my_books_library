<?php
// Start session
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Replace with your database username
define('DB_PASS', ''); // Replace with your database password
define('DB_NAME', 'mybooklibrary'); // Your database name

// Create database connection
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if user is logged in (optional - you might want this for future features)
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT username FROM user WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    // You might want to handle this more gracefully in production
    die("Connection failed: " . $e->getMessage());
}
?>
<?php
// [Your PHP code remains exactly the same]
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Book Library Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7d6aff;
            --secondary-color: #a593ff;
            --accent-color: #ff6b6b;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7ff 0%, #e9e3ff 100%);
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Premium Floating Book Animation */
        .floating-books {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .book {
            position: absolute;
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.9;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
            animation: float 8s ease-in-out infinite;
        }

        .book:nth-child(1) {
            background-image: url('https://cdn-icons-png.flaticon.com/512/560/560216.png');
            width: 80px;
            height: 80px;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
            animation-duration: 9s;
        }

        .book:nth-child(2) {
            background-image: url('https://cdn-icons-png.flaticon.com/512/560/560218.png');
            width: 100px;
            height: 100px;
            top: 70%;
            left: 10%;
            animation-delay: 0.5s;
            animation-duration: 11s;
        }

        .book:nth-child(3) {
            background-image: url('https://cdn-icons-png.flaticon.com/512/560/560219.png');
            width: 120px;
            height: 120px;
            top: 20%;
            left: 80%;
            animation-delay: 1s;
            animation-duration: 10s;
        }

        .book:nth-child(4) {
            background-image: url('https://cdn-icons-png.flaticon.com/512/560/560213.png');
            width: 90px;
            height: 90px;
            top: 80%;
            left: 75%;
            animation-delay: 1.5s;
            animation-duration: 12s;
        }

        .book:nth-child(5) {
            background-image: url('https://cdn-icons-png.flaticon.com/512/560/560215.png');
            width: 110px;
            height: 110px;
            top: 50%;
            left: 15%;
            animation-delay: 2s;
            animation-duration: 8s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-40px) rotate(5deg);
            }
            100% {
                transform: translateY(0) rotate(0deg);
            }
        }

        /* Particle Animation */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(125, 106, 255, 0.2);
            border-radius: 50%;
            animation: particle-float linear infinite;
        }

        @keyframes particle-float {
            from {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 0.6;
            }
            to {
                transform: translateY(-100vh) translateX(20vw);
                opacity: 0;
            }
        }

        /* Main Content */
        .library-container {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform-style: preserve-3d;
            transition: all 0.5s ease;
        }

        .library-container:hover {
            transform: perspective(500px) translateZ(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .library-container h1 {
            color: #4a3f7d;
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .library-container p {
            color: #6c63a0;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .library-container button {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff;
            padding: 18px 45px;
            font-size: 1.2rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(125, 106, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .library-container button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(125, 106, 255, 0.4);
        }

        .library-container button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .library-container button:hover::before {
            left: 100%;
        }

        /* Floating Text Animation */
        .floating-text {
            position: absolute;
            font-size: 5rem;
            font-weight: 800;
            color: rgba(125, 106, 255, 0.05);
            z-index: 0;
            user-select: none;
            animation: text-float 20s linear infinite;
        }

        @keyframes text-float {
            0% {
                transform: translateX(-100%) translateY(-50%);
            }
            100% {
                transform: translateX(100%) translateY(50%);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Floating Books Animation -->
        <div class="floating-books">
            <div class="book"></div>
            <div class="book"></div>
            <div class="book"></div>
            <div class="book"></div>
            <div class="book"></div>
        </div>

        <!-- Particle Animation -->
        <div class="particles" id="particles"></div>

        <!-- Floating Background Text -->
        <div class="floating-text">MY LIBRARY</div>
        <div class="floating-text" style="top:30%; animation-delay:5s;">READ MORE</div>
        <div class="floating-text" style="top:70%; animation-duration:25s;">BOOKS</div>

        <!-- Main Content -->
        <div class="library-container">
            <h1>Welcome to Your Library</h1>
            <p>Discover, read, and organize your favorite books</p>
            <button onclick="navigateToHomePage()">Enter Library</button>
        </div>
    </div>

    <script>
        function navigateToHomePage() {
            window.location.href = "homepage.php";
        }

        // Generate particles dynamically
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random properties
                const size = Math.random() * 10 + 5;
                const posX = Math.random() * 100;
                const duration = Math.random() * 15 + 10;
                const delay = Math.random() * 10;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.bottom = `-${size}px`;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                
                particlesContainer.appendChild(particle);
            }
        });
    </script>
</body>
</html>