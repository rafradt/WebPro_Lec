<?php
$servername = "sql208.bytecluster.com"; 
$username = "root"; 
$password = ""; 
$dbname = "event_management"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>