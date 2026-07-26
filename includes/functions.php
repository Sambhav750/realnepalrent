<?php
function getRecentBookings($conn, $limit = 5) {
    $sql = "SELECT b.BookingID, b.Booking_Date, b.Booking_Status, 
                   c.Brand, c.Model, cust.C_Name
            FROM bookings b
            JOIN cars c ON b.CarID = c.CarID
            JOIN customers cust ON b.CustomerID = cust.CustomerID
            WHERE b.Booking_Status != 'Cancelled'
            ORDER BY b.Booking_Date DESC
            LIMIT $limit";
    
    $result = $conn->query($sql);
    $bookings = [];
    
    while ($row = $result->fetch_assoc()) {
        $time_ago = time_ago($row['Booking_Date']);
        $bookings[] = [
            'name' => $row['C_Name'],
            'car' => $row['Brand'] . ' ' . $row['Model'],
            'time' => $time_ago,
            'status' => $row['Booking_Status']
        ];
    }
    
    return $bookings;
}

function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

function getAvailableCarsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) FROM cars WHERE Availability_Status = 'Available'");
    return $result->fetch_row()[0];
}
?>