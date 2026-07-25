<?php
include 'includes/header.php';
include 'config/db_connect.php';

$carID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($carID == 0) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM cars WHERE CarID = $carID";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$car = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
    $total_price = $total_days * $car['Price_Per_Day'];

    $check_sql = "SELECT * FROM bookings 
                  WHERE CarID = $carID 
                  AND Booking_Status != 'Cancelled'
                  AND (
                      (Start_Date <= '$end_date' AND End_Date >= '$start_date')
                  )";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $error = "Car is not available for the selected dates.";
    } else {
        $_SESSION['booking_data'] = [
            'carID' => $carID,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_days' => $total_days,
            'total_price' => $total_price
        ];
        header("Location: booking.php");
        exit();
    }
}

$review_sql = "SELECT r.*, c.C_Name FROM reviews r 
               JOIN customers c ON r.CustomerID = c.CustomerID 
               WHERE r.CarID = $carID AND r.Is_Approved = 1 
               ORDER BY r.Review_Date DESC LIMIT 10";
$reviews = $conn->query($review_sql);

$rating_sql = "SELECT AVG(Rating) as avg_rating, COUNT(*) as total_reviews 
               FROM reviews WHERE CarID = $carID AND Is_Approved = 1";
$rating_result = $conn->query($rating_sql);
$rating_data = $rating_result->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'] ?? 0;
?>

<div class="container">
    <div class="car-detail-page">
        <a href="index.php" class="btn btn-small">← Back to Cars</a>

        <div class="car-detail">
            <div class="car-detail-image">
                <?php if (!empty($car['Image'])): ?>
                    <img src="assets/images/<?php echo $car['Image']; ?>" alt="<?php echo $car['Brand'] . ' ' . $car['Model']; ?>">
                <?php else: ?>
                    <img src="assets/images/car-placeholder.jpg" alt="Car">
                <?php endif; ?>
            </div>

            <div class="car-detail-info">
                <h1><?php echo $car['Brand'] . ' ' . $car['Model']; ?></h1>
                <div class="car-meta">
                    <span><?php echo $car['Car_Type']; ?></span>
                    <span><?php echo $car['Fuel_Type']; ?></span>
                    <span><?php echo $car['Seating_Capacity']; ?> seats</span>
                </div>
                <div class="car-price">
                    NPR <?php echo number_format($car['Price_Per_Day']); ?> <span>/ day</span>
                </div>
                <div class="car-rating">
                    ⭐ <?php echo $avg_rating; ?> / 5 (<?php echo $total_reviews; ?> reviews)
                </div>
                <div class="car-description">
                    <h4>Description</h4>
                    <p>Experience the <?php echo $car['Brand'] . ' ' . $car['Model']; ?> for your journey. 
                    This <?php echo $car['Car_Type']; ?> offers <?php echo $car['Seating_Capacity']; ?> seats 
                    and runs on <?php echo $car['Fuel_Type']; ?>.</p>
                </div>
                <div class="car-status">
                    <?php if ($car['Availability_Status'] == 'Available'): ?>
                        <span class="status-available">Available Now</span>
                    <?php else: ?>
                        <span class="status-booked">Currently Booked</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($car['Availability_Status'] == 'Available'): ?>
            <div class="booking-section">
                <h3>Book This Car</h3>
                <?php if (isset($_SESSION['CustomerID'])): ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="booking-form">
                            <div class="form-group">
                                <label>Pickup Date</label>
                                <input type="date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Return Date</label>
                                <input type="date" name="end_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" name="book" class="btn btn-primary">Book Now</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Login</a> or <a href="register.php">Register</a> to book this car.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['CustomerID'])): ?>
            <?php
            $check_sql = "SELECT * FROM bookings 
                          WHERE CustomerID = " . $_SESSION['CustomerID'] . " 
                          AND CarID = $carID 
                          AND Booking_Status = 'Completed' 
                          LIMIT 1";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0):
                $booking = $check_result->fetch_assoc();
            ?>
                <div class="review-section">
                    <h3>Write a Review</h3>
                    <form method="POST" action="review.php">
                        <input type="hidden" name="car_id" value="<?php echo $carID; ?>">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['BookingID']; ?>">
                        
                        <div class="form-group">
                            <label>Your Rating</label>
                            <div class="star-rating">
                                <input type="radio" name="rating" value="5" id="star5"><label for="star5">⭐</label>
                                <input type="radio" name="rating" value="4" id="star4"><label for="star4">⭐</label>
                                <input type="radio" name="rating" value="3" id="star3"><label for="star3">⭐</label>
                                <input type="radio" name="rating" value="2" id="star2"><label for="star2">⭐</label>
                                <input type="radio" name="rating" value="1" id="star1"><label for="star1">⭐</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="comment" rows="4" placeholder="Share your experience with this car..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="review-section">
                    <p class="review-note">You need to complete a booking for this car before you can review it.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="review-section">
                <p class="review-note">Please <a href="login.php">login</a> to write a review.</p>
            </div>
        <?php endif; ?>

        <div class="reviews-section">
            <h3>Customer Reviews</h3>
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <strong><?php echo $review['C_Name']; ?></strong>
                            <span class="review-rating">⭐ <?php echo $review['Rating']; ?> / 5</span>
                            <span class="review-date"><?php echo date('M d, Y', strtotime($review['Review_Date'])); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($review['Comment']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to review!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>