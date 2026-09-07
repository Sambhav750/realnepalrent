<?php
session_start();

if (!isset($_SESSION['AdminID'])) {
    header("Location: login.php");
    exit();
}

include '../config/db_connect.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    
    // Get old status for update history
    $old_sql = "SELECT Booking_Status FROM bookings WHERE BookingID = $booking_id";
    $old_result = $conn->query($old_sql);
    $old_status = $old_result->fetch_assoc()['Booking_Status'];

?>