<?php
include 'database/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_story'])) {
    $storyContent = htmlspecialchars(trim($_POST["story_content"]));
    $userID = $_SESSION['UserID'];

    // Handle file upload
    $targetDir = "uploads/stories/";
    $targetFile = $targetDir . basename($_FILES["story_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["story_image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["story_image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["story_image"]["tmp_name"], $targetFile)) {
            // Insert story into database
            $sql = "INSERT INTO stories (UserID, StoryContent, StoryImage) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iss", $userID, $storyContent, $targetFile);
                if ($stmt->execute()) {
                    echo "Story uploaded successfully.";
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Query to fetch stories
$sql = "SELECT stories.StoryID, stories.UserID, stories.StoryContent, stories.StoryImage, stories.StoryTime, user.username AS story_username, user.photo AS story_photo
        FROM stories
        JOIN user ON stories.UserID = user.UserID
        ORDER BY stories.StoryTime DESC";
$result = mysqli_query($conn, $sql);

// Display stories
if (mysqli_num_rows($result) > 0) {
    echo '<div class="stories">';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="story">';
        echo '<img src="' . htmlspecialchars($row['story_photo']) . '" alt="User Photo" class="story-photo">';
        echo '<div class="story-content">';
        echo '<h4>' . htmlspecialchars($row['story_username']) . '</h4>';
        echo '<p>' . htmlspecialchars($row['StoryContent']) . '</p>';
        if (!empty($row['StoryImage'])) {
            echo '<img src="' . htmlspecialchars($row['StoryImage']) . '" alt="Story Image" class="story-image">';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<p>No stories available.</p>';
}
mysqli_close($conn);
?>