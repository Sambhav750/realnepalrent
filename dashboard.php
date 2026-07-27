<?php
include 'includes/header.php';
include 'config/db_connect.php';

if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['CustomerID'];

$sql = "SELECT b.*, c.Brand, c.Model, c.Car_Type, c.Price_Per_Day 
        FROM bookings b 
        JOIN cars c ON b.CarID = c.CarID 
        WHERE b.CustomerID = $customerID 
        ORDER BY b.Booking_Date DESC";
$bookings = $conn->query($sql);

$user_sql = "SELECT * FROM customers WHERE CustomerID = $customerID";
$user = $conn->query($user_sql)->fetch_assoc();

$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE CustomerID = $customerID")->fetch_row()[0];
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE CustomerID = $customerID AND Booking_Status = 'Pending'")->fetch_row()[0];
$completed_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE CustomerID = $customerID AND Booking_Status = 'Completed'")->fetch_row()[0];
?>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, <strong><?php echo $_SESSION['C_Name']; ?></strong>!</p>
        </div>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3><?php echo $total_bookings; ?></h3>
            <p>Total Bookings</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $pending_bookings; ?></h3>
            <p>Pending</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $completed_bookings; ?></h3>
            <p>Completed</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $user['C_Phone'] ?: 'N/A'; ?></h3>
            <p>Phone</p>
        </div>
    </div>

    <div class="profile-section">
        <h3>My Profile</h3>
        <table>
            <tr><td><strong>Name:</strong></td><td><?php echo $user['C_Name']; ?></td></tr>
            <tr><td><strong>Email:</strong></td><td><?php echo $user['C_Email']; ?></td></tr>
            <tr><td><strong>Phone:</strong></td><td><?php echo $user['C_Phone'] ?: 'Not provided'; ?></td></tr>
            <tr><td><strong>Registered:</strong></td><td><?php echo date('M d, Y', strtotime($user['CreatedAt'])); ?></td></tr>
        </table>
    </div>

    <h3>My Bookings</h3>
    <?php if ($bookings->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Car</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['BookingID']; ?></td>
                            <td><?php echo $row['Brand'] . ' ' . $row['Model']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['Start_Date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['End_Date'])); ?></td>
                            <td>NPR <?php echo number_format($row['Total_Price']); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                if ($row['Booking_Status'] == 'Pending') $status_class = 'status-pending';
                                elseif ($row['Booking_Status'] == 'Confirmed') $status_class = 'status-confirmed';
                                elseif ($row['Booking_Status'] == 'Completed') $status_class = 'status-completed';
                                elseif ($row['Booking_Status'] == 'Cancelled') $status_class = 'status-cancelled';
                                ?>
                                <span class="booking-status <?php echo $status_class; ?>">
                                    <?php echo $row['Booking_Status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="invoice.php?id=<?php echo $row['BookingID']; ?>" class="btn btn-small btn-primary">Invoice</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>You have no bookings yet. <a href="index.php">Browse cars</a> and make your first booking!</p>
    <?php endif; ?>
</div>

<?php
$extra_css = '
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    .dashboard-header h1 {
        font-size: 28px;
    }
    .profile-section {
        background: white;
        padding: 20px 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin: 20px 0 30px;
    }
    .profile-section table {
        margin-top: 10px;
        width: 100%;
        max-width: 500px;
    }
    .profile-section td {
        padding: 6px 10px;
    }
    .bookings-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .bookings-table th {
        background: #1a1a2e;
        color: white;
        padding: 12px 15px;
        text-align: left;
    }
    .bookings-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    .bookings-table tr:hover td {
        background: #f9f9f9;
    }
    .booking-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-confirmed {
        background: #cce5ff;
        color: #004085;
    }
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
    .table-responsive {
        overflow-x: auto;
    }
    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            gap: 10px;
        }
        .bookings-table th, .bookings-table td {
            padding: 8px 10px;
            font-size: 13px;
        }
    }
';
echo '<style>' . $extra_css . '</style>';

include 'includes/footer.php';
?>