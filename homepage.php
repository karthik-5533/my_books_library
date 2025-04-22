<?php
// Ensure no output before session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php'; 

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <link rel="stylesheet" type="text/css" href="styles.css">
    
</head>
<body>

<!-- Original Sidebar Navigation (unchanged) -->
<div class="sidebar">
    <!-- My Library Section -->
    <h2 onclick="toggleDropdown('libraryDropdown')">My Library <i class="fa fa-chevron-down"></i></h2>
    <div id="libraryDropdown" class="dropdown-content">
        <a href="homepage.php?q=1" class="<?php if(@$_GET['q']==1) echo'active'; ?>">Books</a>
        <a href="homepage.php?q=2" class="<?php if(@$_GET['q']==2) echo'active'; ?>">Favorites</a>
        <a href="homepage.php?q=4" class="<?php if(@$_GET['q']==4) echo'active'; ?>">Highlights</a>
        <a href="homepage.php?q=5" class="<?php if(@$_GET['q']==5) echo'active'; ?>">Trash</a>
    </div>

    <!-- Shelf Section -->
    <h3 onclick="toggleDropdown('shelfDropdown')">Shelf <i class="fa fa-chevron-down"></i></h3>
    <div id="shelfDropdown" class="dropdown-content">
        <a href="#">Study</a>
        <a href="#">Work</a>
        <a href="#">Entertainment</a>
    </div>

    <!-- Progress Tracking Section -->
    <h3 onclick="toggleDropdown('progressDropdown')">Progress Tracking <i class="fa fa-chevron-down"></i></h3>
    <div id="progressDropdown" class="dropdown-content">
        <a href="homepage.php?q=6" class="<?php if(@$_GET['q']==6) echo'active'; ?>">Reading Goals</a>
        <a href="#">Current Progress</a>
        <a href="#">Completed Books</a>
    </div>
</div>

<div class="main">
    <!-- Rest of your main content remains exactly the same -->
    <div class="header">
        <div class="search-bar">
            <input type="text" placeholder="Search books..." id="searchInput">
            <button onclick="searchBooks()"><i class="fa fa-search"></i></button>
        </div>
        <div class="header-buttons">
            <a href="import.php" 
               class="btn btnn-primary" 
               style="background-color: rgb(89, 59, 107); color:white; position: absolute; top: 20px; left: 1200px;">
               Import 📚
            </a>
            <a href="logout.php" 
               class="btn btnn-primary"
               style="background-color: rgb(212, 38, 38); width: 100px ;color: white ; position: absolute; top: 20px; left: 1300px;">
               Logout
            </a>
        </div>
    </div>

    <div class="booklist">
        <?php
        // Include the appropriate content based on q parameter
        if (!isset($_GET['q']) || $_GET['q'] == 1) {
            include('book.php');  // Main books listing
        }?>

<?php
        // Include the appropriate content based on q parameter
        if (!isset($_GET['q']) || $_GET['q'] == 2) {
            include('favourite.php');  // Main books listing
        }?>
        <?php
        // Include the appropriate content based on q parameter
        if (!isset($_GET['q']) || $_GET['q'] == 3) {
            include('notes.php');  // Main books listing
        }?>
        <?php
        // Include the appropriate content based on q parameter
        if (!isset($_GET['q']) || $_GET['q'] == 4) {
            include('notes.php');  // Main books listing
        }?>
        <?php
        // Include the appropriate content based on q parameter
        if (!isset($_GET['q']) || $_GET['q'] == 5) {
            include('trash.php');  // Main books listing
        }?>

    </div>
</div>

<script>
// Fixed dropdown toggle function (unchanged)
function toggleDropdown(id) {
    var dropdown = document.getElementById(id);
    dropdown.classList.toggle("show");
    
    // Toggle chevron icon
    var header = dropdown.previousElementSibling;
    var icon = header.querySelector('i');
    if (dropdown.classList.contains("show")) {
        icon.classList.remove("fa-chevron-down");
        icon.classList.add("fa-chevron-up");
    } else {
        icon.classList.remove("fa-chevron-up");
        icon.classList.add("fa-chevron-down");
    }
}

// Close dropdowns when clicking outside (unchanged)
window.onclick = function(event) {
    if (!event.target.matches('.sidebar h2') && !event.target.matches('.sidebar h3')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
                var icon = openDropdown.previousElementSibling.querySelector('i');
                icon.classList.remove("fa-chevron-up");
                icon.classList.add("fa-chevron-down");
            }
        }
    }
}

// Your existing search function (unchanged)
function searchBooks() {
    var input = document.getElementById('searchInput').value.toLowerCase();
    var books = document.querySelectorAll('.book');

    books.forEach(function(book) {
        var title = book.querySelector('h3').textContent.toLowerCase();
        book.style.display = title.includes(input) ? '' : 'none';
    });
}
</script>

</body>
</html>
<?php
// Close connection at the end
mysqli_close($conn);
?>