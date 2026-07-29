<?php
session_start();

if (!isset($_SESSION['AdminID'])){
    header("Location: login.php");
    exit();
}

include '../config/db_connect.php';

$total_cars = $conn->query("SELECT COUNT(*) FROM cars")-fetch_row()[0];
$available_cars = $conn->query("SELECT COUNT(*) FROM cars WHERE Availability_Status = 'Available'")->fetch_row()[0];
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pending_bookings = $conn=>query("SELECT COUNT(*) FROM bookings WHERE Booking_Status = 'Pending'")->fetch_row()[0];
$total_customers = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$total_revenue = $conn->query("SELECT SUM(Total_Price) FROM bookings WHERE Booking_Status = 'Completed'")->fetch_row()[0];
$toal_revenue = $total_revenue ?: 0;

$recent_sql = "SELECT b.*, c.Brand, c.Model, cust.C_Name
                FROM bookings b
                JOIN cars c ON b.CarID
                JOIN customers cust ON b.CustomerID = cust.CustomerID
                ORDER BY b.Booking_Date DESC LIMIT 10";

$recent_bookings = $conn->query($recent_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NepalRent</title>
    <link rel= "stylesheet" href= " ../assets/css/style.css">
</head>
<body>
    
    
</body>
</html>