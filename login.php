<?php
session_start();
include 'config/db_connect.php';
include 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM customers WHERE C_Email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['C_Password'])) {
            $_SESSION['CustomerID'] = $user['CustomerID'];
            $_SESSION['C_Name'] = $user['C_Name'];
            $_SESSION['C_Email'] = $user['C_Email'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "Email not found. Please register first.";
    }
}
?>

<div class="container">
    <div class="auth-container">
        <h2>Login to NepalRent</h2>
        <p>Welcome back! Login to access your account</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>