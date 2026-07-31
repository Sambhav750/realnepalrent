<?php
include 'includes/header.php';
include 'config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['CustomerID'];

// Get user's bookings
$sql = "SELECT b.*, c.Brand, c.Model, c.Car_Type, c.Price_Per_Day 
        FROM bookings b 
        JOIN cars c ON b.CarID = c.CarID 
        WHERE b.CustomerID = $customerID 
        ORDER BY b.Booking_Date DESC";
$bookings = $conn->query($sql);

// Get user details
$user_sql = "SELECT * FROM customers WHERE CustomerID = $customerID";
$user = $conn->query($user_sql)->fetch_assoc();

// Get booking stats
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE CustomerID = $customerID")->fetch_row()[0];
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE CustomerID = $customerID AND Booking_Status = 'Pending'")->fetch_row()[0];
$completed_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE CustomerID = $customerID AND Booking_Status = 'Completed'")->fetch_row()[0];
?>

<div class="container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, <strong><?php echo $_SESSION['C_Name']; ?></strong>!</p>
        </div>
        
    </div>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="number"><?php echo $total_bookings; ?></div>
            <div class="label">Total Bookings</div>
        </div>
        <div class="stat-card pending">
            <div class="number"><?php echo $pending_bookings; ?></div>
            <div class="label">Pending</div>
        </div>
        <div class="stat-card completed">
            <div class="number"><?php echo $completed_bookings; ?></div>
            <div class="label">Completed</div>
        </div>
        <div class="stat-card phone">
            <div class="number"><?php echo $user['C_Phone'] ?: 'N/A'; ?></div>
            <div class="label">Phone</div>
        </div>
    </div>

    <!-- Profile Section (Card) -->
    <div class="profile-section card">
        <h3>My Profile</h3>
        <table>
            <tr><td><strong>Name:</strong></td><td><?php echo $user['C_Name']; ?></td></tr>
            <tr><td><strong>Email:</strong></td><td><?php echo $user['C_Email']; ?></td></tr>
            <tr><td><strong>Phone:</strong></td><td><?php echo $user['C_Phone'] ?: 'Not provided'; ?></td></tr>
            <tr><td><strong>Registered:</strong></td><td><?php echo date('M d, Y', strtotime($user['CreatedAt'])); ?></td></tr>
        </table>
    </div>

    <!-- Bookings Table -->
    <h3>My Bookings</h3>
    <?php if ($bookings->num_rows > 0): ?>
        <div class="table-container">
            <table>
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
                                <span class="status-badge <?php echo $status_class; ?>">
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

<style>
    /* Add any missing styles to match admin */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    .dashboard-header h1 {
        font-size: 28px;
        margin: 0;
    }
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    .stat-card .number {
        font-size: 32px;
        font-weight: 800;
        color: #f97316;
    }
    .stat-card .label {
        color: #64748b;
        font-size: 14px;
        margin-top: 4px;
    }
    .stat-card.pending .number { color: #f59e0b; }
    .stat-card.completed .number { color: #22c55e; }
    .stat-card.phone .number { font-size: 24px; color: #8b5cf6; }

    .profile-section {
        background: white;
        padding: 20px 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        margin: 20px 0 30px;
    }
    .profile-section table {
        width: 100%;
        max-width: 500px;
        margin-top: 10px;
    }
    .profile-section td {
        padding: 6px 10px;
    }
    .table-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        margin-top: 15px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    table th {
        background: #f4f6f9;
        text-align: left;
        padding: 10px 12px;
        font-weight: 600;
    }
    table td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
    }
    table tr:hover td {
        background: #f9f9f9;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .btn-small {
        padding: 4px 10px;
        font-size: 12px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        background: #f97316;
        color: white;
    }
    .btn-small:hover {
        background: #ea580c;
    }
    @media (max-width: 768px) {
        .dashboard-stats {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 480px) {
        .dashboard-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>