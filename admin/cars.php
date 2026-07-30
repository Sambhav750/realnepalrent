<?php

if (!isset($_SESSION['AdminID'])){
    header("Location: login.php");
    exit();
}

include '../config/db_connect.php';

// delete car
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM cars WHERE CarID = $id");
    header("Location: cars.php?msg=Car deleted");
    exit();
}

//Edit car - get data
$edit_car = null;
$is_edit = false;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])){
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM cars WHERE CarID = $edit_id");
    if ($edit_result->num_rows >0){
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