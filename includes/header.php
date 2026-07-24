<?php

if (session_status() === PHP_SESSION_NONE){
    session_start();
}

global $conn;
if (isset($conn) && $conn) {
    $result = $conn->query("SELECT COUNT(*) FROM cars WHERE Availability_Status = 'Available'");
    $available_count = $result->fetch_row()[0];
} else {
    $available_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NepalRent - Online Car Rental System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-left">
                <a href="index.php" class="logo">gitNepalRent</a>
                
            </div>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#cars">Cars</a></li>
                    <?php if (isset($_SESSION['CustomerID'])): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php" class="btn-primary-nav">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="btn-primary-nav">Register</a></li>
                    <?php endif; ?>
                    <li>
                        <button id="darkModeToggle" class="dark-toggle" aria-label="Toggle dark mode">
                            🌙
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
</body>
</html>