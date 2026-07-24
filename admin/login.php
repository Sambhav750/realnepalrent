<?php
session_start();
include '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Find admin by username
    $sql = "SELECT * FROM admins WHERE Username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $admin['Password'])) {
            $_SESSION['AdminID'] = $admin['AdminID'];
            $_SESSION['AdminUsername'] = $admin['Username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Admin not found";
    }
}
?>