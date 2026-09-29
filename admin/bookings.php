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
// Get filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$sql = "SELECT b.*, c.Brand, c.Model, cust.C_Name, cust.C_Email, cust.C_Phone 
        FROM bookings b 
        JOIN cars c ON b.CarID = c.CarID 
        JOIN customers cust ON b.CustomerID = cust.CustomerID 
        WHERE 1=1";

if (!empty($status_filter)) {
    $sql .= " AND b.Booking_Status = '$status_filter'";
}

if (!empty($search)) {
    $sql .= " AND (b.BookingID LIKE '%$search%' OR cust.C_Name LIKE '%$search%' OR c.Brand LIKE '%$search%')";
}

$sql .= " ORDER BY b.Booking_Date DESC";
$bookings = $conn->query($sql);

// Get status counts
$total_count = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pending_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE Booking_Status = 'Pending'")->fetch_row()[0];
$confirmed_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE Booking_Status = 'Confirmed'")->fetch_row()[0];
$completed_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE Booking_Status = 'Completed'")->fetch_row()[0];
$cancelled_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE Booking_Status = 'Cancelled'")->fetch_row()[0];
?>

        
<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings - NepalRent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-body {
            display: flex;
            min-height: 100vh;
            background: #f4f6f9;
        }
        .sidebar {
            width: 250px;
            background: #1a1a2e;
            color: white;
            padding: 20px 0;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
        }
        .sidebar .logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #333;
            margin-bottom: 20px;
            display: grid;
        }
        .sidebar .logo h2 {
            color: var(--primary);
            margin: 0;
        }
        .sidebar .logo p {
            color: #888;
            font-size: 12px;
            margin: 5px 0 0;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li {
            padding: 12px 25px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .sidebar ul li:hover {
            background: #2a2a4e;
            border-left-color: var(--primary);
        }
        .sidebar ul li.active {
            background: #2a2a4e;
            border-left-color: var(--primary);
        }
        .sidebar ul li a {
            color: #ccc;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar ul li a:hover {
            color: white;
        }
        .sidebar ul li a .icon {
            font-size: 18px;
            width: 25px;
        }
        .sidebar .logout-link {
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 15px;
        }
        .sidebar .logout-link a {
            color: #ef4444;
        }
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 25px;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .main-header h1 {
            font-size: 24px;
            margin: 0;
        }
        .main-header .admin-info {
            color: #888;
            font-size: 14px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
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
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar a {
            text-decoration: none;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            background: #e9ecef;
            color: #333;
        }
        .filter-bar a:hover {
            background: #dee2e6;
        }
        .filter-bar a.active {
            background: var(--primary);
            color: white;
        }
        .search-bar {
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-form {
            display: inline-flex;
            gap: 5px;
            align-items: center;
        }
        .status-form select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-small {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: #ea580c;
        }
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
        }
        .no-data {
            text-align: center;
            color: #888;
            padding: 30px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                padding: 10px 0;
            }
            .sidebar .logo h2,
            .sidebar .logo p,
            .sidebar ul li a span {
                display: none;
            }
            .sidebar ul li {
                padding: 12px 15px;
                text-align: center;
            }
            .sidebar ul li a .icon {
                font-size: 22px;
                width: auto;
            }
            .main-content {
                margin-left: 60px;
            }
            .filter-bar {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="admin-body">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>NepalRent</h2>
                <p>Admin Panel</p>
            </div>
            <ul>
                <li><a href="index.php"><span class="icon"></span><span>Dashboard</span></a></li>
                <li><a href="cars.php"><span class="icon"></span><span>Cars</span></a></li>
                <li class="active"><a href="bookings.php"><span class="icon"></span><span>Bookings</span></a></li>
                <li><a href="customers.php"><span class="icon"></span><span>Customers</span></a></li>
                <li><a href="reports.php"><span class="icon"></span><span>Reports</span></a></li>
                <li class="logout-link"><a href="logout.php"><span class="icon"></span><span>Logout</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="main-header">
                <h1>Manage Bookings</h1>
                <span class="admin-info">Welcome, <?php echo $_SESSION['AdminUsername']; ?></span>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <a href="bookings.php" class="<?php echo empty($status_filter) ? 'active' : ''; ?>">All (<?php echo $total_count; ?>)</a>
                <a href="bookings.php?status=Pending" class="<?php echo $status_filter == 'Pending' ? 'active' : ''; ?>">Pending (<?php echo $pending_count; ?>)</a>
                <a href="bookings.php?status=Confirmed" class="<?php echo $status_filter == 'Confirmed' ? 'active' : ''; ?>">Confirmed (<?php echo $confirmed_count; ?>)</a>
                <a href="bookings.php?status=Completed" class="<?php echo $status_filter == 'Completed' ? 'active' : ''; ?>">Completed (<?php echo $completed_count; ?>)</a>
                <a href="bookings.php?status=Cancelled" class="<?php echo $status_filter == 'Cancelled' ? 'active' : ''; ?>">Cancelled (<?php echo $cancelled_count; ?>)</a>
                
                <div class="search-bar">
                    <form method="GET" action="">
                        <?php if (!empty($status_filter)): ?>
                            <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Search bookings..." value="<?php echo $search; ?>">
                        <button type="submit" class="btn-small btn-primary">Search</button>
                    </form>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="table-container">
                <?php if ($bookings->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Pickup</th>
                                <th>Return</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $booking['BookingID']; ?></td>
                                    <td>
                                        <strong><?php echo $booking['C_Name']; ?></strong>
                                        <br><small style="color:#888;"><?php echo $booking['C_Email']; ?></small>
                                    </td>
                                    <td><?php echo $booking['Brand'] . ' ' . $booking['Model']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['Start_Date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['End_Date'])); ?></td>
                                    <td>NPR <?php echo number_format($booking['Total_Price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($booking['Booking_Status']); ?>">
                                            <?php echo $booking['Booking_Status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['BookingID']; ?>">
                                            <select name="new_status">
                                                <option value="Pending" <?php echo $booking['Booking_Status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Confirmed" <?php echo $booking['Booking_Status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirm</option>
                                                <option value="Completed" <?php echo $booking['Booking_Status'] == 'Completed' ? 'selected' : ''; ?>>Complete</option>
                                                <option value="Cancelled" <?php echo $booking['Booking_Status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancel</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-small btn-primary">Update</button>
                                        </form>
                                        <a href="../invoice.php?id=<?php echo $booking['BookingID']; ?>" target="_blank" class="btn-small btn-info">Invoice</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">No bookings found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>