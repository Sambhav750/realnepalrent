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
    
</head>
<body>
    
</body>
</html>