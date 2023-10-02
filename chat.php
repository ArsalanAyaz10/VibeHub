<!DOCTYPE html>
<html>
<head>
    <title>Chat App</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>
<body>
<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch the list of friends from the database
$stmt = $mysqli->prepare("SELECT username FROM Users WHERE user_id != ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row["username"];
}
$stmt->close();

// Fetch the chat messages from the database
$stmt = $mysqli->prepare("SELECT sender_id, receiver_id, message_content, profile_picture FROM Chats JOIN Users ON Chats.sender_id = Users.user_id WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
$stmt->bind_param("iiii", $_SESSION["user_id"], $receiver_id, $receiver_id, $_SESSION["user_id"]);
$receiver_id = 2; // Example receiver ID
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

// Close the database connection
$mysqli->close();
?>

<div class="container">
    <div class="friends-list">
        <div class="friends-list-bar">
            <a class="profile-link" href="profile.php">User Profile</a>
            <a href="login.php"><button class="logout-button">Logout</button></a>
            <a href="posts.php" class="posts-link">Posts</a>
        </div>
        <h2>Friends List</h2>
        <ul id="friends-list">
            <?php foreach ($friends as $friend) : ?>
                <li class="friend-item"><?php echo $friend; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="chat-box">
        <div class="message-area" id="message-area">
            <?php foreach ($messages as $message) : ?>
                <div class="message <?php echo ($message['sender_id'] == $_SESSION['user_id']) ? 'sender' : 'receiver'; ?>">
                    <div class="profile-picture">
                        <img src="<?php echo ($message['profile_picture'] != "") ? $message['profile_picture'] : 'default_profile_picture.jpg'; ?>" alt="Profile Picture">
                    </div>
                    <div class="message-content"><?php echo $message['message_content']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="input-area">
            <input type="text" class="message-input" id="message-input" placeholder="Type your message">
            <button class="send-button" id="send-button">Send</button>
        </div>
    </div>
</div>


<script>
// JavaScript code goes here
document.addEventListener("DOMContentLoaded", function() {
    const messageArea = document.getElementById("message-area");
    const messageInput = document.getElementById("message-input");
    const sendButton = document.getElementById("send-button");
    const friendsList = document.getElementById("friends-list");

    // Function to add a new message to the message area
    function addMessage(content, sender) {
        const messageDiv = document.createElement("div");
        messageDiv.className = `message ${sender}`;

        const messageContent = document.createElement("div");
        messageContent.className = "message-content";
        messageContent.textContent = content;

        messageDiv.appendChild(messageContent);
        messageArea.appendChild(messageDiv);
    }

    // Function to send a new message
    function sendMessage() {
        const message = messageInput.value.trim();
        if (message !== "") {
            const selectedFriend = friendsList.querySelector(".selected");
            if (selectedFriend) {
                const receiverId = selectedFriend.dataset.id;

                // Perform AJAX request to send the message to the server
                fetch("send_message.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `receiver_id=${encodeURIComponent(receiverId)}&message_content=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    // Check if the message was sent successfully
                    if (data.success) {
                        // Add the new message to the message area
                        addMessage(message, "sender");
                        // Clear the message input
                        messageInput.value = "";
                    } else {
                        console.error(data.error);
                    }
                })
                .catch(error => {
                    console.error("An error occurred:", error);
                });
            } else {
                console.error("No recipient selected.");
            }
        }
    }

    // Event listener for the send button click
    sendButton.addEventListener("click", sendMessage);

    // Event listener for the Enter key press in the message input
    messageInput.addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendMessage();
        }
    });

    // Event listener for friend selection
    friendsList.addEventListener("click", function(event) {
        const selectedFriend = event.target.closest(".friend-item");
        if (selectedFriend) {
            // Remove the "selected" class from all friends
            friendsList.querySelectorAll(".friend-item").forEach(item => item.classList.remove("selected"));
            // Add the "selected" class to the selected friend
            selectedFriend.classList.add("selected");
        }
    });
});
</script>
</body>
</html>
