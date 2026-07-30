<?php
session_start();

if (!isset($_SESSION['AdminID'])) {
    header("Location: login.php");
    exit();
}

include '../config/db_connect.php';

// Delete car
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM cars WHERE CarID = $id");
    header("Location: cars.php?msg=Car deleted");
    exit();
}

// Edit car - get data
$edit_car = null;
$is_edit = false;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM cars WHERE CarID = $edit_id");
    if ($edit_result->num_rows > 0) {
        $edit_car = $edit_result->fetch_assoc();
        $is_edit = true;
    }
}

// Add or Edit car
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $car_type = $_POST['car_type'];
    $fuel_type = $_POST['fuel_type'];
    $seating = $_POST['seating'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $image = $_POST['image'];
    
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $sql = "UPDATE cars SET 
                Brand = '$brand', 
                Model = '$model', 
                Car_Type = '$car_type', 
                Fuel_Type = '$fuel_type', 
                Seating_Capacity = '$seating', 
                Price_Per_Day = '$price', 
                Availability_Status = '$status',
                Image = '$image'
                WHERE CarID = $edit_id";
        $conn->query($sql);
        header("Location: cars.php?msg=Car updated");
        exit();
    } else {
        $sql = "INSERT INTO cars (Brand, Model, Car_Type, Fuel_Type, Seating_Capacity, Price_Per_Day, Availability_Status, Image) 
                VALUES ('$brand', '$model', '$car_type', '$fuel_type', '$seating', '$price', '$status', '$image')";
        $conn->query($sql);
        header("Location: cars.php?msg=Car added");
        exit();
    }
}

$cars = $conn->query("SELECT * FROM cars ORDER BY CreatedAt DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Cars - NepalRent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root{
            --primary: #245481;
        }

        .admin-body { 
            display: flex; min-height: 100vh; background: #f4f6f9;
         }
        .sidebar { 
            width: 250px;
            background: #1a1a2e;
            color: white; padding: 20px 0;
            min-height: 100vh; position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto; }
        
        .sidebar .logo { 
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #333;
            margin-bottom: 20px;
            display: grid; 
        }
        
        .sidebar .logo h2 {
             color: var(--primary); margin: 0; }

        .sidebar .logo p {
             color: #888; font-size: 12px; margin: 5px 0 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }

        .sidebar ul li {
             padding: 12px 25px; border-left: 3px solid transparent; transition: all 0.3s; }

        .sidebar ul li:hover {
             background: #2a2a4e; border-left-color: var(--primary); }

        .sidebar ul li.active {
             background: #2a2a4e; border-left-color: var(--primary); }

        .sidebar ul li a {
             color: #ccc; text-decoration: none; display: flex; align-items: center; gap: 12px; }

        .sidebar ul li a:hover {
             color: white; }

        .sidebar ul li a .icon {
             font-size: 18px; width: 25px; }

        .sidebar .logout-link {
             margin-top: 30px; border-top: 1px solid #333; padding-top: 15px; }

        .sidebar .logout-link a {
             color: var(--primary); }

        .main-content {
             margin-left: 250px; flex: 1; padding: 25px; }

        .main-header {
             display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        
         .main-header h1 {
             font-size: 24px; margin: 0; }

        .main-header .admin-info {
             color: #888; font-size: 14px; }

        .btn-small {
             padding: 4px 10px; font-size: 12px; border-radius: 5px; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        
         .btn-success { 
            background: #22c55e; color: white; }

        .btn-success:hover {
             background: #16a34a; }

        .btn-danger {
             background: var(--primary); color: white; }

        .btn-danger:hover {
             background: var(--primary); }

        .btn-warning {
             background: var(--primary); color: white; }

        .btn-warning:hover {
             background: var(--primary); }

        .btn-primary {
             background: var(--primary); color: white; }

        .btn-primary:hover {
             background: var(--primary); }

        .table-container {
             background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow-x: auto; }
        
        table {
             width: 100%; border-collapse: collapse; font-size: 14px; }

        table th {
             background: #f4f6f9; text-align: left; padding: 10px 12px; font-weight: 600; }

        table td {
            
        padding: 10px 12px; border-bottom: 1px solid #eee; }

        table tr:hover td {
             background: #f9f9f9; }

        .status-badge {
             display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }

        .status-available {
            background: #dcfce7; color: #166534; }

        .status-booked {
    
             background: #fee2e2; color: #991b1b; }

        .status-maintenance { 
            background: #fef3c7; color: #92400e; }

        .form-container {
             background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .form-grid {
        display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }

        .form-group {
             margin-bottom: 0; }

        .form-group label {
             display: block; font-weight: 600; font-size: 13px; margin-bottom: 3px; }

        .form-group input, .form-group select {
             width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }

        .form-actions {
             display: flex; gap: 10px; align-items: end; }

        .alert {
             padding: 10px 15px; border-radius: 5px; margin-bottom: 15px; }

        .alert-success {
             background: #dcfce7; color: #166534; border: 1px solid #86efac; }

        .btn {
            padding: 8px 20px; border-radius: 5px; border: none; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
        .no-data { 
            text-align: center; color: #888; padding: 30px; }

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
                <li class="active"><a href="cars.php"><span class="icon"></span><span>Cars</span></a></li>
                <li><a href="bookings.php"><span class="icon"></span><span>Bookings</span></a></li>
                <li><a href="customers.php"><span class="icon"></span><span>Customers</span></a></li>
                <li><a href="reports.php"><span class="icon"></span><span>Reports</span></a></li>
                <li class="logout-link"><a href="logout.php"><span class="icon"></span><span>Logout</span></a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="main-header">
                <h1>Manage Cars</h1>
                <span class="admin-info">Welcome, <?php echo $_SESSION['AdminUsername']; ?></span>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="form-container">
                <h3><?php echo $is_edit ? 'Edit Car' : 'Add New Car'; ?></h3>
                <form method="POST">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_car['CarID']; ?>">
                    <?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" required value="<?php echo $is_edit ? $edit_car['Brand'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" name="model" required value="<?php echo $is_edit ? $edit_car['Model'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Car Type</label>
                            <select name="car_type" required>
                                <option value="SUV" <?php echo ($is_edit && $edit_car['Car_Type'] == 'SUV') ? 'selected' : ''; ?>>SUV</option>
                                <option value="Sedan" <?php echo ($is_edit && $edit_car['Car_Type'] == 'Sedan') ? 'selected' : ''; ?>>Sedan</option>
                                <option value="Hatchback" <?php echo ($is_edit && $edit_car['Car_Type'] == 'Hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                                
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fuel Type</label>
                            <select name="fuel_type" required>
                                <option value="Petrol" <?php echo ($is_edit && $edit_car['Fuel_Type'] == 'Petrol') ? 'selected' : ''; ?>>Petrol</option>
                                <option value="Diesel" <?php echo ($is_edit && $edit_car['Fuel_Type'] == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                                <option value="Electric" <?php echo ($is_edit && $edit_car['Fuel_Type'] == 'Electric') ? 'selected' : ''; ?>>Electric</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Seating Capacity</label>
                            <input type="number" name="seating" required value="<?php echo $is_edit ? $edit_car['Seating_Capacity'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Price Per Day (NPR)</label>
                            <input type="number" name="price" required value="<?php echo $is_edit ? $edit_car['Price_Per_Day'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="Available" <?php echo ($is_edit && $edit_car['Availability_Status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                <option value="Booked" <?php echo ($is_edit && $edit_car['Availability_Status'] == 'Booked') ? 'selected' : ''; ?>>Booked</option>
                                <option value="Maintenance" <?php echo ($is_edit && $edit_car['Availability_Status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Image (filename)</label>
                            <input type="text" name="image" placeholder="car-image.jpg" value="<?php echo $is_edit ? $edit_car['Image'] : ''; ?>">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update Car' : 'Add Car'; ?></button>
                            <?php if ($is_edit): ?>
                                <a href="cars.php" class="btn btn-secondary" style="background:#888;">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cars Table -->
            <div class="table-container">
                <h3>All Cars</h3>
                <?php if ($cars->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($car = $cars->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $car['CarID']; ?></td>
                                    <td>
                                        <?php if ($car['Image']): ?>
                                            <img src="../assets/images/<?php echo $car['Image']; ?>" style="width:50px; height:35px; object-fit:cover; border-radius:4px;">
                                        <?php else: ?>
                                            <span style="color:#888;">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $car['Brand']; ?></td>
                                    <td><?php echo $car['Model']; ?></td>
                                    <td><?php echo $car['Car_Type']; ?></td>
                                    <td>NPR <?php echo number_format($car['Price_Per_Day']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($car['Availability_Status']); ?>">
                                            <?php echo $car['Availability_Status']; ?>
                                        </span>
                                    </td>
                                    <td>   
                                        <a href="cars.php?edit=<?php echo $car['CarID']; ?>" class="btn-small btn-warning">Edit</a>
                                        <a href="cars.php?delete=<?php echo $car['CarID']; ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">No cars added yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>