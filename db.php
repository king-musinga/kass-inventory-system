<?php

$servername = "localhost";

$username = "root"; 

$password = "Th079011r"; 

$dbname = "stock"; 



// Create connection

$conn = mysqli_connect($servername, $username, $password, $dbname);



// Check connection

if (!$conn) {

    die("Connection failed: " . mysqli_connect_error());

}







?>

