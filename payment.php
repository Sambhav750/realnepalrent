<?php
session_start();
include 'includes/header.php';
include 'config/db_connect.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$bookingID = isset($_GET['bookingID']) ? intval($_GET['bookingID']) : 0;

if ($bookingID == 0) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT b.*, c.Brand, c.Model, c.Car_Type, c.Price_Per_Day, cust.C_Name, cust.C_Email, cust.C_Phone 
        FROM bookings b 
        JOIN cars c ON b.CarID = c.CarID 
        JOIN customers cust ON b.CustomerID = cust.CustomerID 
        WHERE b.BookingID = $bookingID AND b.CustomerID = " . $_SESSION['CustomerID'];
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_payment'])) {
    $payment_method = $_POST['payment_method'];
    $advance_amount = $booking['Total_Price'] * 0.2;
    
    $pay_sql = "INSERT INTO payments (BookingID, Amount, Payment_Type, Payment_Method, Payment_Status) 
                VALUES ('$bookingID', '$advance_amount', 'Advance', '$payment_method', 'Paid')";
    
    if ($conn->query($pay_sql) === TRUE) {
        $update_sql = "UPDATE bookings SET Booking_Status = 'Confirmed' WHERE BookingID = $bookingID";
        $conn->query($update_sql);
        
        header("Location: payment_success.php?bookingID=$bookingID");
        exit();
    }
}
?>

<div class="container">
    <div class="payment-container">
        <h1>Payment</h1>
        
        <div class="payment-layout">
            <div class="payment-details">
                <h3>Booking Details</h3>
                <div class="detail-item">
                    <span class="label">Booking ID:</span>
                    <span class="value">#<?php echo $bookingID; ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Car:</span>
                    <span class="value"><?php echo $booking['Brand'] . ' ' . $booking['Model']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Customer:</span>
                    <span class="value"><?php echo $booking['C_Name']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Dates:</span>
                    <span class="value"><?php echo date('M d', strtotime($booking['Start_Date'])); ?> - <?php echo date('M d, Y', strtotime($booking['End_Date'])); ?></span>
                </div>
                
                <h3>Payment Summary</h3>
                <div class="detail-item">
                    <span class="label">Total Amount:</span>
                    <span class="value">NPR <?php echo number_format($booking['Total_Price']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Advance (20%):</span>
                    <span class="value">NPR <?php echo number_format($booking['Total_Price'] * 0.2); ?></span>
                </div>
                <div class="detail-item total">
                    <span class="label">Amount to Pay:</span>
                    <span class="value">NPR <?php echo number_format($booking['Total_Price'] * 0.2); ?></span>
                </div>
            </div>
            
            <div class="payment-form">
                <h3>Make Payment</h3>
                <p class="payment-note">This is a mock payment for demonstration</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="eSewa">eSewa</option>
                            <option value="Cash">Cash (Pay at pickup)</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Khalti">Khalti</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Card Number (Mock)</label>
                        <input type="text" placeholder="4242 4242 4242 4242">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label>Expiry (Mock)</label>
                            <input type="text" placeholder="MM/YY">
                        </div>
                        <div class="form-group half">
                            <label>CVV (Mock)</label>
                            <input type="text" placeholder="123">
                        </div>
                    </div>
                    
                    <button type="submit" name="make_payment" class="btn btn-primary btn-large">
                        Pay NPR <?php echo number_format($booking['Total_Price'] * 0.2); ?>
                    </button>
                </form>
                
                <p class="secure-note">Your payment is secure and encrypted</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>