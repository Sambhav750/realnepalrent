<?php
session_start();

if (!isset($_SESSION['AdminID'])) {
    header("Location: login.php");
    exit();
}

include '../config/db_connect.php';

$total_cars = $conn->query("SELECT COUNT(*) FROM cars")->fetch_row()[0];
$available_cars = $conn->query("SELECT COUNT(*) FROM cars WHERE Availability_Status = 'Available'")->fetch_row()[0];
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE Booking_Status = 'Pending'")->fetch_row()[0];
$total_customers = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$total_revenue = $conn->query("SELECT SUM(Total_Price) FROM bookings WHERE Booking_Status = 'Completed'")->fetch_row()[0];
$total_revenue = $total_revenue ?: 0;

$recent_sql = "SELECT b.*, c.Brand, c.Model, cust.C_Name 
               FROM bookings b 
               JOIN cars c ON b.CarID = c.CarID 
               JOIN customers cust ON b.CustomerID = cust.CustomerID 
               ORDER BY b.Booking_Date DESC LIMIT 10";
$recent_bookings = $conn->query($recent_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - NepalRent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>

        :root {
            --aprimary: #245481;
        }
        /* Admin Styles */
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
            color: var(--aprimary);
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
            border-left-color: #f97316;
        }
        .sidebar ul li.active {
            background: #2a2a4e;
            border-left-color: #f97316;
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
        }
        .main-header h1 {
            font-size: 24px;
            margin: 0;
        }
        .main-header .admin-info {
            color: #888;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: var(--aprimary);
        }
        .stat-card .label {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        .stat-card.pending .number { color: var(--aprimary); }
        .stat-card.revenue .number { color: var(--aprimary); }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        .table-container h3 {
            margin-top: 0;
            margin-bottom: 15px;
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
        }
        .btn-primary { background: var(--aprimary); color: white; }
        .btn-primary:hover { background: var(--aprimary); }

        
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
                <li class="active"><a href="index.php"><span class="icon">📊</span><span>Dashboard</span></a></li>
                <li><a href="cars.php"><span class="icon">🚙</span><span>Cars</span></a></li>
                <li><a href="bookings.php"><span class="icon">📅</span><span>Bookings</span></a></li>
                <li><a href="customers.php"><span class="icon">👤</span><span>Customers</span></a></li>
                <li><a href="reports.php"><span class="icon">📈</span><span>Reports</span></a></li>
                <li class="logout-link"><a href="logout.php"><span class="icon">🚪</span><span>Logout</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="main-header">
                <h1>Dashboard</h1>
                <span class="admin-info">Welcome, <?php echo $_SESSION['AdminUsername']; ?></span>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo $total_cars; ?></div>
                    <div class="label">Total Cars</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $available_cars; ?></div>
                    <div class="label">Available Cars</div>
                </div>
                <div class="stat-card pending">
                    <div class="number"><?php echo $pending_bookings; ?></div>
                    <div class="label">Pending Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $total_customers; ?></div>
                    <div class="label">Total Customers</div>
                </div>
                <div class="stat-card revenue">
                    <div class="number">NPR <?php echo number_format($total_revenue); ?></div>
                    <div class="label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $total_bookings; ?></div>
                    <div class="label">Total Bookings</div>
                </div>
            </div>

            <div class="table-container">
                <h3>Recent Bookings</h3>
                <?php if ($recent_bookings->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['BookingID']; ?></td>
                                    <td><?php echo $row['C_Name']; ?></td>
                                    <td><?php echo $row['Brand'] . ' ' . $row['Model']; ?></td>
                                    <td><?php echo date('M d', strtotime($row['Start_Date'])); ?> - <?php echo date('M d', strtotime($row['End_Date'])); ?></td>
                                    <td>NPR <?php echo number_format($row['Total_Price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($row['Booking_Status']); ?>">
                                            <?php echo $row['Booking_Status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #888;">No bookings yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>