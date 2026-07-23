<?php
$servername = "localhosst";
$username = "root";
$password = "";
$dbname = "nepalrent_db";

//basically i created a connection to the database
$conn = new mysqli($servername,$username, $password, $dbname);

if($conn ->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>