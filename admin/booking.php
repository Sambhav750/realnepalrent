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

     // If booking is confirmed, update car availability
        if ($new_status == 'Confirmed') {
            $car_sql = "SELECT CarID FROM bookings WHERE BookingID = $booking_id";
            $car_result = $conn->query($car_sql);
            $car_id = $car_result->fetch_assoc()['CarID'];
            $conn->query("UPDATE cars SET Availability_Status = 'Booked' WHERE CarID = $car_id");
        }
        
        // If booking is cancelled, make car available again
        if ($new_status == 'Cancelled') {
            $car_sql = "SELECT CarID FROM bookings WHERE BookingID = $booking_id";
            $car_result = $conn->query($car_sql);
            $car_id = $car_result->fetch_assoc()['CarID'];
            $conn->query("UPDATE cars SET Availability_Status = 'Available' WHERE CarID = $car_id");
        }

         // If booking is completed, make car available again and record final payment
        if ($new_status == 'Completed') {
            $car_sql = "SELECT CarID FROM bookings WHERE BookingID = $booking_id";
            $car_result = $conn->query($car_sql);
            $car_id = $car_result->fetch_assoc()['CarID'];
            $conn->query("UPDATE cars SET Availability_Status = 'Available' WHERE CarID = $car_id");
            
            // Get total amount for this booking
            $total_sql = "SELECT Total_Price FROM bookings WHERE BookingID = $booking_id";
            $total_result = $conn->query($total_sql);
            $total = $total_result->fetch_assoc()['Total_Price'];
            
            // Calculate advance (20%) and final (80%)
            $advance = $total * 0.2;
            $final = $total - $advance;
            
            // Check if final payment already exists
            $check_pay = "SELECT * FROM payments WHERE BookingID = $booking_id AND Payment_Type = 'Final'";
            $check_result = $conn->query($check_pay);
            
            if ($check_result->num_rows == 0) {
                $pay_sql = "INSERT INTO payments (BookingID, Amount, Payment_Type, Payment_Method, Payment_Status) 
                            VALUES ('$booking_id', '$final', 'Final', 'Cash', 'Paid')";
                $conn->query($pay_sql);
            }
        }
        
        header("Location: bookings.php?msg=Booking status updated successfully");
        exit();
    } else {
        $error = "Error updating status: " . $conn->error;
    }
}

        

?>