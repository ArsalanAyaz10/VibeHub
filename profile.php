<!DOCTYPE html>
<html>
<head>
    <title>Profile Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
// Start the session and check if the user is logged in
session_start();
if (!isset($_SESSION["user_id"])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Include the database configuration file
require_once "config.php";

// Retrieve user profile data from the database based on user ID
$user_id = $_SESSION["user_id"];

// Prepare and bind the SQL statement
$stmt = $mysqli->prepare("SELECT username, profile_picture FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

// Execute the statement and check for success
if ($stmt->execute()) {
    $stmt->bind_result($username, $profile_picture);
    $stmt->fetch();

    // Display or process the user profile data
    // echo "User ID: " . $user_id . "<br>";
    // echo "Username: " . $username . "<br>";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement
$stmt->close();

// Close the database connection
$mysqli->close();
?>


<div class="container">
    <h2><?php echo $username; ?></h2>
    <div class="profile-picture">
        <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
    </div>
    <p>Avid Chat App User</p>
    <a href="login.php"><button class="logout-button">Logout</button></a>
    <a href="chat.php" class="chat-link">Go to Chat App</a>
</div>
</body>
</html>
