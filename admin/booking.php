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

     // Update booking status
    $sql = "UPDATE bookings SET Booking_Status = '$new_status' WHERE BookingID = $booking_id";
    
    if ($conn->query($sql) === TRUE) {
        // Log the update in booking_updates table
        $update_sql = "INSERT INTO booking_updates (BookingID, Updated_By, Update_Type, Old_Value, New_Value, Reason) 
                       VALUES ('$booking_id', 'Admin', 'Status_Change', '$old_status', '$new_status', 'Status updated by admin')";
        $conn->query($update_sql);
        

?>