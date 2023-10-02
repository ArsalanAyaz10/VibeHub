<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database configuration file
    require_once "config.php";
    
    // Prepare and bind the SQL statement
    $stmt = $mysqli->prepare("SELECT user_id, password FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    
    // Set the values based on the form input
    $username = $_POST["username"];
    
    // Execute the statement and check for success
    if ($stmt->execute()) {
        $stmt->store_result();
        
        // Check if a row is returned
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();
            
            // Verify the password
            if (password_verify($_POST["password"], $hashed_password)) {
                // Start a new session and store user data
                session_start();
                $_SESSION["user_id"] = $user_id;
                
                // Redirect to the chat page after a delay
                header("refresh:2;url=chat.php");
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Error: " . $stmt->error;
    }
    
    // Close the statement and database connection
    $stmt->close();
    $mysqli->close();
}
?>


<div class="container">
    <h2>Login</h2>
    <form method="POST" action="">
        <input type="text" name="username" id="username" placeholder="Username" required>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
    <div class="signup-link">
        Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
    <div class="message">
        <?php echo $message; ?>
    </div>
</div>
</body>
</html>
