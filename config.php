<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "chat_app_db";
define("PROFILE_PICTURES_DIR", "profile_pictures/");

// Create a new mysqli instance
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}
?>
