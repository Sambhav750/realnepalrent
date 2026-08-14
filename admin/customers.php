<?php
session_start();

if (!isset($_SESSION['AdminID'])) {
    header("Location: login.php");
    exit();
}

include '../config/db_connect.php';

// Verify license
if (isset($_GET['verify']) && is_numeric($_GET['verify'])) {
    $id = $_GET['verify'];
    $status = $_GET['status']; // Verified or Rejected
    $adminID = $_SESSION['AdminID'];
    
    $sql = "UPDATE customers SET 
            License_Status = '$status', 
            License_Verified_At = NOW(), 
            License_Verified_By = $adminID 
            WHERE CustomerID = $id";
    $conn->query($sql);
    header("Location: customers.php?msg=License $status");
    exit();
}

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM customers WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (C_Name LIKE '%$search%' OR C_Email LIKE '%$search%' OR C_Phone LIKE '%$search%')";
}

$sql .= " ORDER BY CreatedAt DESC";
$customers = $conn->query($sql);
$total_customers = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers - NepalRent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-body { display: flex; min-height: 100vh; background: #f4f6f9; }
        .sidebar { width: 250px; background: #1a1a2e; color: white; padding: 20px 0; min-height: 100vh; position: fixed; left: 0; top: 0; bottom: 0; overflow-y: auto; }
        .sidebar .logo { text-align: center; padding: 20px 0; border-bottom: 1px solid #333; margin-bottom: 20px; }
        .sidebar .logo h2 { color: var(--primary); margin: 0; }
        .sidebar .logo p { color: #888; font-size: 12px; margin: 5px 0 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li { padding: 12px 25px; border-left: 3px solid transparent; transition: all 0.3s; }
        .sidebar ul li:hover { background: #2a2a4e; border-left-color: var(--primary); }
        .sidebar ul li.active { background: #2a2a4e; border-left-color: var(--primary); }
        .sidebar ul li a { color: #ccc; text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .sidebar ul li a:hover { color: white; }
        .sidebar ul li a .icon { font-size: 18px; width: 25px; }
        .sidebar .logout-link { margin-top: 30px; border-top: 1px solid #333; padding-top: 15px; }
        .sidebar .logout-link a { color: var(--primary); }
        .main-content { margin-left: 250px; flex: 1; padding: 25px; }
        .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .main-header h1 { font-size: 24px; margin: 0; }
        .main-header .admin-info { color: #888; font-size: 14px; }
        .btn-small { padding: 4px 10px; font-size: 12px; border-radius: 5px; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-success { background: var(--primary); color: white; }
        .btn-success:hover { background: var(--primary); }
        .btn-danger { background: var(--primary); color: white; }
        .btn-danger:hover { background: var(--primary); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary); }
        .btn-secondary { background: #64748b; color: white; }
        .btn-secondary:hover { background: #475569; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        table th { background: #f4f6f9; text-align: left; padding: 10px 12px; font-weight: 600; }
        table td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        table tr:hover td { background: #f9f9f9; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-verified { background: #dcfce7; color: var(--primary); }
        .status-pending { background: #fef3c7; color: var(--primary); }
        .status-rejected { background: #fee2e2; color: var(--primary); }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; }
        .stat-card .number { font-size: 28px; font-weight: bold; color: var(--primary); }
        .stat-card .label { color: #888; font-size: 14px; margin-top: 5px; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; flex: 1; }
        .no-data { text-align: center; color: #888; padding: 30px; }
        .alert { padding: 10px 15px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #dcfce7; color: var(--primary); border: 1px solid #86efac; }
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
                <li><a href="bookings.php"><span class="icon"></span><span>Bookings</span></a></li>
                <li class="active"><a href="customers.php"><span class="icon"></span><span>Customers</span></a></li>
                <li><a href="reports.php"><span class="icon"></span><span>Reports</span></a></li>
                <li class="logout-link"><a href="logout.php"><span class="icon"></span><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="main-header">
                <h1>Manage Customers</h1>
                <span class="admin-info">Welcome, <?php echo $_SESSION['AdminUsername']; ?></span>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo $total_customers; ?></div>
                    <div class="label">Total Customers</div>
                </div>
                <?php
                $verified = $conn->query("SELECT COUNT(*) FROM customers WHERE License_Status = 'Verified'")->fetch_row()[0];
                $pending = $conn->query("SELECT COUNT(*) FROM customers WHERE License_Status = 'Pending'")->fetch_row()[0];
                ?>
                <div class="stat-card">
                    <div class="number" style="color:#22c55e;"><?php echo $verified; ?></div>
                    <div class="label">Verified</div>
                </div>
                <div class="stat-card">
                    <div class="number" style="color:#f59e0b;"><?php echo $pending; ?></div>
                    <div class="label">Pending Verification</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo date('F Y'); ?></div>
                    <div class="label">Current Month</div>
                </div>
            </div>

            <!-- Search -->
            <div class="search-bar">
                <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                    <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="customers.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="table-container">
                <h3>All Registered Customers</h3>
                <?php if ($customers->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered</th>
                                <th>License</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $customer['CustomerID']; ?></td>
                                    <td><strong><?php echo $customer['C_Name']; ?></strong></td>
                                    <td><?php echo $customer['C_Email']; ?></td>
                                    <td><?php echo $customer['C_Phone'] ?: 'N/A'; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($customer['CreatedAt'])); ?></td>
                                    <td>
                                        <?php if ($customer['License_Image']): ?>
                                            <?php 
                                            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/NEPALRENT2.0/assets/uploads/licenses/' . $customer['License_Image'];
                                            if (file_exists($file_path)): 
                                            ?>
                                                <a href="/NEPALRENT2.0/assets/uploads/licenses/<?php echo $customer['License_Image']; ?>" target="_blank" class="btn btn-small btn-primary">View</a>
                                            <?php else: ?>
                                                <span style="color:#ff6b6b;">File missing</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#888;">No license</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $customer['License_Status'] ?? 'Pending';
                                        $class = '';
                                        if ($status == 'Verified') $class = 'status-verified';
                                        elseif ($status == 'Rejected') $class = 'status-rejected';
                                        else $class = 'status-pending';
                                        ?>
                                        <span class="status-badge <?php echo $class; ?>"><?php echo $status; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($status != 'Verified'): ?>
                                            <a href="customers.php?verify=<?php echo $customer['CustomerID']; ?>&status=Verified" class="btn-small btn-success">Verify</a>
                                        <?php endif; ?>
                                        <?php if ($status != 'Rejected' && $status != 'Verified'): ?>
                                            <a href="customers.php?verify=<?php echo $customer['CustomerID']; ?>&status=Rejected" class="btn-small btn-danger">Reject</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">No customers found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>