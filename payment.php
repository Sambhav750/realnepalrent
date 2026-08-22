<?php
session_start();
include 'includes/header.php';
include 'config/db_connect.php';


// Step 1: Check if user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

// Step 2: Get booking ID from URL
$bookingID = isset($_GET['bookingID']) ? intval($_GET['bookingID']) : 0;

if ($bookingID == 0) {
    header("Location: index.php");
    exit();
}

// Step 3: Get booking details
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

// Step 4: Process payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_payment'])) {
    $payment_method = $_POST['payment_method'];
    $advance_amount = $booking['Total_Price'] * 0.2;
    
    // Step 5: Insert payment record
    $pay_sql = "INSERT INTO payments (BookingID, Amount, Payment_Type, Payment_Method, Payment_Status) 
                VALUES ('$bookingID', '$advance_amount', 'Advance', '$payment_method', 'Paid')";
    
    if ($conn->query($pay_sql) === TRUE) {
        // Step 6: Update booking status
        $update_sql = "UPDATE bookings SET Booking_Status = 'Confirmed' WHERE BookingID = $bookingID";
        $conn->query($update_sql);
        
        // Step 7: Store booking ID in session for invoice
        $_SESSION['booking_id'] = $bookingID;
        
        // Step 8: Redirect to success
        header("Location: payment_success.php?bookingID=$bookingID");
        exit();
    } else {
        $error = "Payment failed. Please try again.";
    }
}
?>

<div class="container">
    <div class="payment-container">
        <h1>Payment</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="payment-layout">
            <!-- Left: Booking Details -->
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
                
                <h3 style="margin-top: 20px;">Payment Summary</h3>
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
            
            <!-- Right: Payment Form -->
            <div class="payment-form">
                <h3>Make Payment</h3>
                <p class="payment-note"> This is a mock payment for demonstration</p>
                
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
                
                <p class="secure-note"> Your payment is secure and encrypted</p>
            </div>
        </div>
    </div>
</div>

<style>
.payment-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px 0;
}
.payment-container h1 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 28px;
}
.payment-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
.payment-details,
.payment-form {
    background: white;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}
body.dark-mode .payment-details,
body.dark-mode .payment-form {
    background: #1e293b;
    border-color: #334155;
}
.payment-details h3,
.payment-form h3 {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}
body.dark-mode .payment-details h3,
body.dark-mode .payment-form h3 {
    border-bottom-color: #334155;
}
.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
}
body.dark-mode .detail-item {
    border-bottom-color: #334155;
}
.detail-item .label {
    color: #64748b;
    font-weight: 500;
}
body.dark-mode .detail-item .label {
    color: #94a3b8;
}
.detail-item .value {
    font-weight: 600;
}
body.dark-mode .detail-item .value {
    color: white;
}
.detail-item.total {
    border-bottom: 2px solid var(--primary);
    padding: 15px 0;
    font-size: 18px;
}
.detail-item.total .label {
    color: #1e293b;
    font-weight: 600;
}
body.dark-mode .detail-item.total .label {
    color: white;
}
.detail-item.total .value {
    color: var(--primary);
    font-weight: 700;
}
.payment-note {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 20px;
    background: #f8fafc;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}
body.dark-mode .payment-note {
    background: #334155;
    color: #94a3b8;
}
.payment-form .form-group {
    margin-bottom: 15px;
}
.payment-form .form-group label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 5px;
    color: #334155;
}
body.dark-mode .payment-form .form-group label {
    color: #cbd5e1;
}
.payment-form .form-group input,
.payment-form .form-group select {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.3s;
}
body.dark-mode .payment-form .form-group input,
body.dark-mode .payment-form .form-group select {
    background: #334155;
    border-color: #475569;
    color: white;
}
.payment-form .form-group input:focus,
.payment-form .form-group select:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
.btn-large {
    padding: 14px 20px;
    font-size: 18px;
    width: 100%;
    margin-top: 10px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s;
}
.btn-large:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.secure-note {
    text-align: center;
    margin-top: 15px;
    color: #64748b;
    font-size: 14px;
}
body.dark-mode .secure-note {
    color: #94a3b8;
}
.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}
.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
body.dark-mode .alert-danger {
    background: #7f1d1d;
    color: #fca5a5;
    border-color: #991b1b;
}

</style>

<?php include 'includes/footer.php'; ?>