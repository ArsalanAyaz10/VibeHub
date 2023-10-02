<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database configuration file
    require_once "config.php";
    
    // Prepare and bind the SQL statement
    $stmt = $mysqli->prepare("INSERT INTO Users (username, password, profile_picture) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $profile_picture);
    
    // Set the values based on the form input
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Encrypt the password

    // Handle profile picture upload
    $profile_picture = "";
    if ($_FILES["profile_picture"]["error"] === 0) {
        $target_dir = "profile_pictures/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "Invalid profile picture file.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["profile_picture"]["size"] > 500000) {
            $message = "Profile picture file is too large. Maximum size allowed is 500KB.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedExtensions)) {
            $message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $message .= " The profile picture was not uploaded.";
        } else {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES["profile_picture"]["profile-picture"], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $message = "Error uploading the profile picture.";
            }
        }
    }

    // Execute the statement and check for success
    if ($stmt->execute()) {
        $message = "Sign up successful!";
        // Redirect to the login page after a delay
        header("refresh:2;url=login.php");
    } else {
        $message = "Error: " . $stmt->error;
    }
    
    // Close the statement and database connection
    $stmt->close();
    $mysqli->close();
}
?>


<div class="container">
    <h2>Sign Up</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="text" name="username" id="username" placeholder="Username" required>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <input type="file" name="profile_picture" accept="image/*" required>
        <input type="submit" value="Sign Up">
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </div>
    <div class="message">
        <?php echo $message; ?>
    </div>
</div>
</body>
</html>
