<?php

$host = 'localhost';       
$dbname = 'ezpc_db';       
$username = 'root';        
$password = '';           

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
