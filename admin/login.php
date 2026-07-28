<?php
session_start();
include '../config/db_connect.php';

if (isset($_SESSION['AdminID'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE Username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
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

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - NepalRent</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #1a1a2e;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 380px;
            max-width: 90%;
        }
        .login-container h1 {
            text-align: center;
            color: #245481;
            margin-bottom: 5px;
        }
        .login-container .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .login-container .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            background: #fee2e2;
            color: #245481;
            border: 1px solid #fca5a5;
        }
        .login-container .form-group {
            margin-bottom: 15px;
        }
        .login-container .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .login-container .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .login-container .btn {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background: #245481;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .login-container .btn:hover {
            background: #245481;
        }
        .login-container .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-container .back-link a {
            color: #888;
            text-decoration: none;
        }
        .login-container .back-link a:hover {
            color: #245481;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>NepalRent</h1>
        <p class="subtitle">Admin Login</p>
        
        <?php if (isset($error)): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" >
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" >
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Back to Website</a>
        </div>
    </div>
</body>
</html>