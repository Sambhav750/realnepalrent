<?php
include 'includes/header.php';
include 'config/db_connect.php';
include 'includes/functions.php';

$available_count = getAvailableCarsCount($conn);
$recent_bookings = getRecentBookings($conn, 5);

// Get filter values from URL
$car_type = isset($_GET['car_type']) ? $_GET['car_type'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Build SQL query with filters
$sql = "SELECT * FROM cars WHERE Availability_Status = 'Available'";

if (!empty($car_type)) {
    $sql .= " AND Car_Type = '$car_type'";
}
if (!empty($brand)) {
    $sql .= " AND Brand = '$brand'";
}
if (!empty($min_price)) {
    $sql .= " AND Price_Per_Day >= $min_price";
}
if (!empty($max_price)) {
    $sql .= " AND Price_Per_Day <= $max_price";
}
$sql .= " ORDER BY CreatedAt DESC";
$result = $conn->query($sql);

// Get car types for filter dropdown
$types_result = $conn->query("SELECT DISTINCT Car_Type FROM cars");
// Get car brands for filter dropdown
$brands_result = $conn->query("SELECT DISTINCT Brand FROM cars");
?>


    <section class="ticker-section">
        <div class="container">
            <div class="ticker-wrapper">
                
                <div class="ticker-container">
                    <div class="ticker-content">
                        <?php if (!empty($recent_bookings)): ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <span class="ticker-item">
                                    <span class="ticker-name"><?php echo $booking['name']; ?></span>
                                    booked
                                    <span class="ticker-car"><?php echo $booking['car']; ?></span>
                                    <span class="ticker-time"><?php echo $booking['time']; ?></span>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="ticker-item">No recent bookings yet. Be the first!</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

  

   <!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
              
                <h1>Rent the Perfect Car for <span>Your Journey</span></h1>
                <p>Explore Nepal with our reliable and affordable car rental service.</p>
                <div class="hero-tagline">
                    <span class="line"></span>
                    <span>Trusted by 10,000+ customers</span>
                </div>
                <div class="hero-actions">
                    <a href="#cars" class="btn-hero">Browse Cars</a>
                </div><br>
                <div class="available-badge">
                    <span class="count-number"><?php echo $available_count; ?></span>
                    <span class="count-text">Cars Available Now!</span>
                </div>
            </div>
            <div class="hero-image">
                <div class="car-display">
                    <img src="assets/images/hyundai.png" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Filter Section -->
<section class="filter-section">
    <div class="container">
        <div class="filter-box">
            <h3>Filter Cars</h3>
            <form method="GET" action="index.php">
                <div class="filter-grid">
                    <div class="form-group">
                        <label>Car Type</label>
                        <select name="car_type">
                            <option value="">All Types</option>
                            <?php while ($type = $types_result->fetch_assoc()): ?>
                                <option value="<?php echo $type['Car_Type']; ?>" <?php echo ($car_type == $type['Car_Type']) ? 'selected' : ''; ?>>
                                    <?php echo $type['Car_Type']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand</label>
                        <select name="brand">
                            <option value="">All Brands</option>
                            <?php while ($brand_row = $brands_result->fetch_assoc()): ?>
                                <option value="<?php echo $brand_row['Brand']; ?>" <?php echo ($brand == $brand_row['Brand']) ? 'selected' : ''; ?>>
                                    <?php echo $brand_row['Brand']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Min Price (NPR)</label>
                        <input type="number" name="min_price" placeholder="0" value="<?php echo $min_price; ?>">
                    </div>
                    <div class="form-group">
                        <label>Max Price (NPR)</label>
                        <input type="number" name="max_price" placeholder="10000" value="<?php echo $max_price; ?>">
                    </div>
                    <div class="form-group" style="display: flex; gap: 10px;">
                        <button type="submit" class="btn">Apply Filter</button>
                        <a href="index.php" class="btn btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
    <section id="cars">
        <div class="container">
            <h2>Available Cars</h2>
            <p class="section-sub">Choose from our fleet of well-maintained vehicles</p>

            <?php if ($result->num_rows > 0): ?>
                <div class="car-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="car-card">
                            <?php if (!empty($row['Image']) && file_exists('assets/images/' . $row['Image'])): ?>
                                <img src="assets/images/<?php echo $row['Image']; ?>" alt="<?php echo $row['Brand'] . ' ' . $row['Model']; ?>">
                            <?php else: ?>
                                <img src="assets/images/car-placeholder.jpg" alt="Car">
                            <?php endif; ?>
                            <div class="car-info">
                                <h3><?php echo $row['Brand'] . ' ' . $row['Model']; ?></h3>
                                <p class="car-details">
                                    <span><?php echo $row['Car_Type']; ?></span>
                                    <span><?php echo $row['Fuel_Type']; ?></span>
                                    <span><?php echo $row['Seating_Capacity']; ?> seats</span>
                                </p>
                                <p class="price">NPR <?php echo number_format($row['Price_Per_Day']); ?> <span>/ day</span></p>
                                <p class="status">
                                    <span class="status-available">Available</span>
                                </p>
                                <a href="car_detail.php?id=<?php echo $row['CarID']; ?>" class="btn btn-primary btn-small">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-cars">
                    <p>No cars available matching your criteria.</p>
                    <a href="index.php" class="btn">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </section>


<?php
include 'includes/footer.php';
?>