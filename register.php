<?php

session_start();

include 'config/db_connect.php'

if($_SERVER["REQUEST_METHOD"]== "POST"){

    // retrive the data from the html 
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password']
    $confirm_password = $_POST['confirm_password']

    if(empty('name') || empty('email') || empty('phone') || empty('password')){
        $error "Please fill all the required fields"
    }

    elseif($password !== $confirm_password){
        $error "The filled up password do not match"
    }

    elseif (strlen($password)<6 ){
        $error "The password need to be above six characters."
    }

    else{
        $check_email = "SELECT * FROM customers WHERE C_Email = '$email'";
        $check_result = $conn -> query($check_email);

        if($check_result-> num_rows > 0 ){
            $error "The email already exists";
        }
        else{
            $hashed_password = hash_password($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO customers (C_Name, C_Email, C_Phone, C_Password) 
                    VALUES ('$name', '$email', '$phone', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                // Success - redirect to login
                header("Location: login.php?msg=Registration Successful. Please Login.");
                exit();
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }


} 