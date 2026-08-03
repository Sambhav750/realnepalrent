<?php
include 'config/db_connect.php';
include 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle license upload
    $license_image = '';
    if (isset($_FILES['license_image']) && $_FILES['license_image']['error'] == 0) {
        $target_dir = "assets/uploads/licenses/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['license_image']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validate file type
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'pdf'])) {
            if (move_uploaded_file($_FILES['license_image']['tmp_name'], $target_file)) {
                $license_image = $file_name;
            }
        }
    }
    
    $error = '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        $check_sql = "SELECT * FROM customers WHERE C_Email = '$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = "Email already registered. Please login.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO customers (C_Name, C_Email, C_Phone, C_Password, License_Image, License_Status) 
                    VALUES ('$name', '$email', '$phone', '$hashed_password', '$license_image', 'Pending')";
            
            if ($conn->query($sql) === TRUE) {
                header("Location: login.php?msg=Registration Successful. Please wait for license verification.");
                exit();
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<div class="container">
    <div class="auth-container">
        <h2>Create Account</h2>
        <p>Join NepalRent to start renting cars</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" minlength="10">
            </div>
            <div class="form-group">
                <label>Password * (min 6 characters)</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" minlength="6" required>
            </div>
            <div class="form-group">
                <label>Driving License (Upload Image) *</label>
                <input type="file" name="license_image" accept="image/*,.pdf" required>
                <small style="color: #888;">Upload a clear image of your driving license</small>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php
include 'includes/footer.php';
?>