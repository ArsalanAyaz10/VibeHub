<!DOCTYPE html>
<html>
<head>
    <title>Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 10px;
            text-align: center;
        }

        .post-form textarea {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 4px;
        }

        .post-form button[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .post-list {
            margin-top: 20px;
        }

        .post {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .username {
            font-weight: bold;
        }

        .post-date {
            color: #888;
            font-size: 12px;
        }

        .post-content {
            word-wrap: break-word;
        }

        .logout-link {
            text-align: right;
            margin-bottom: 20px;
        }

        .logout-link a {
            text-decoration: none;
            color: #333;
            padding: 10px;
            background-color: #e0e0e0;
            border-radius: 4px;
        }

        .chat-link {
            text-align: left;
            margin-bottom: 20px;
        }

        .chat-link a {
            text-decoration: none;
            color: #333;
            display: inline-block;
            padding: 10px;
            background-color: #e0e0e0;
            border-radius: 4px;
        }

        .chat-link a:hover {
            background-color: #d0d0d0;
        }
    </style>
</head>
<body>
<?php
session_start();
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Retrieve all posts from the database
$stmt = $mysqli->prepare("SELECT Users.username, Posts.content, Posts.image, Posts.created_at FROM Posts INNER JOIN Users ON Posts.user_id = Users.user_id ORDER BY Posts.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Function to display a single post
function displayPost($username, $content, $image, $created_at) {
  echo '<div class="post">';
  echo '<div class="post-header">';
  echo '<span class="username">' . $username . '</span>';
  echo '<span class="post-date">' . $created_at . '</span>';
  echo '</div>';
  echo '<div class="post-content">' . $content . '</div>';
  if (!empty($image)) {
      echo '<img src="' . $image . '" class="post-image">';
  }
  echo '</div>';
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the content field is not empty
    if (!empty($_POST["content"])) {
        $content = $_POST["content"];
        $user_id = $_SESSION["user_id"];
        $image = null;

        // Upload image file if provided
        if ($_FILES["image"]["error"] === 0) {
            $imageDir = "uploads/";
            $imageName = $_FILES["image"]["name"];
            $imageTmpName = $_FILES["image"]["tmp_name"];
            $imagePath = $imageDir . $imageName;
            move_uploaded_file($imageTmpName, $imagePath);
            $image = $imagePath;
        }

        // Insert the new post into the database
        $stmt = $mysqli->prepare("INSERT INTO Posts (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $content, $image);
        $stmt->execute();
        $stmt->close();

        // Redirect to the same page to avoid duplicate form submission
        header("Location: posts.php");
        exit();
    }
}
?>

<div class="container">
    <h2>Posts</h2>
    <?php
// Check if the user is logged in
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    
    // Retrieve the username from the database
    $stmt = $mysqli->prepare("SELECT username FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $username = $row["username"];
    $stmt->close();
}
?>

<!-- Display the username at the top of the page -->
<div class="logout-link">
    <a href="logout.php">Logout</a>
</div>

<div class="chat-link">
    <a href="chat.php">Go to Chat</a>
</div>

<div class="logged-in-user">
    Logged in as: <?php echo $username; ?>
</div>

    <div class="post-form">
        <form method="POST" action="" enctype="multipart/form-data">
            <textarea name="content" placeholder="Write something..." required></textarea>
            <input type="file" name="image" accept="image/*">
            <button type="submit">Post</button>
        </form>
    </div>
    <div class="post-list">
        <?php foreach ($posts as $post) : ?>
            <?php displayPost($post["username"], $post["content"], $post["image"], $post["created_at"]); ?>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
