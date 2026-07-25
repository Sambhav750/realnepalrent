<?php
session_start();
include 'includes/header.php';
include 'config/db_connect.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['booking_data'])) {
    header("Location: index.php");
    exit();
}

$booking = $_SESSION['booking_data'];
$customerID = $_SESSION['CustomerID'];

$car_sql = "SELECT * FROM cars WHERE CarID = " . $booking['carID'];
$car = $conn->query($car_sql)->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_booking'])) {
    $check_sql = "SELECT * FROM bookings 
                  WHERE CarID = " . $booking['carID'] . " 
                  AND Booking_Status != 'Cancelled'
                  AND (
                      (Start_Date <= '" . $booking['end_date'] . "' AND End_Date >= '" . $booking['start_date'] . "')
                  )";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows == 0) {
        $insert_sql = "INSERT INTO bookings (CustomerID, CarID, Start_Date, End_Date, Total_Days, Total_Price, Booking_Status) 
                       VALUES ('$customerID', '{$booking['carID']}', '{$booking['start_date']}', '{$booking['end_date']}', '{$booking['total_days']}', '{$booking['total_price']}', 'Pending')";
        
        if ($conn->query($insert_sql) === TRUE) {
            $bookingID = $conn->insert_id;
            unset($_SESSION['booking_data']);
            header("Location: payment.php?bookingID=$bookingID");
            exit();
        }
    }
}
?>

<div class="container">
    <div class="booking-container">
        <h1>Confirm Your Booking</h1>
        
        <div class="booking-summary">
            <h3>Booking Summary</h3>
            
            <div class="booking-details">
                <div class="booking-item">
                    <span class="label">Car:</span>
                    <span class="value"><?php echo $car['Brand'] . ' ' . $car['Model']; ?></span>
                </div>
                <div class="booking-item">
                    <span class="label">Car Type:</span>
                    <span class="value"><?php echo $car['Car_Type']; ?></span>
                </div>
                <div class="booking-item">
                    <span class="label">Pickup Date:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($booking['start_date'])); ?></span>
                </div>
                <div class="booking-item">
                    <span class="label">Return Date:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($booking['end_date'])); ?></span>
                </div>
                <div class="booking-item">
                    <span class="label">Total Days:</span>
                    <span class="value"><?php echo $booking['total_days']; ?> days</span>
                </div>
                <div class="booking-item total">
                    <span class="label">Total Price:</span>
                    <span class="value">NPR <?php echo number_format($booking['total_price']); ?></span>
                </div>
                <div class="booking-item">
                    <span class="label">Advance Payment (20%):</span>
                    <span class="value">NPR <?php echo number_format($booking['total_price'] * 0.2); ?></span>
                </div>
            </div>
            
            <div class="terms">
                <p><strong>Terms & Conditions:</strong></p>
                <ul>
                    <li>A valid driving license is required at pickup</li>
                    <li>Advance payment of 20% is required to confirm booking</li>
                    <li>Cancellation policy: 100% refund if cancelled 48+ hours before pickup</li>
                    <li>Late return charges may apply</li>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" name="confirm_booking" class="btn btn-primary btn-large">
                    Confirm Booking
                </button>
                <a href="car_detail.php?id=<?php echo $booking['carID']; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>