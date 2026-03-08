<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "users";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}
