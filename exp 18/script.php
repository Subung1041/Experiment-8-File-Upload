<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (isset($_POST['submit']) && isset($_FILES['profile_pic'])) {

    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_pic'];

    // Extract file info
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Allowed file types
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($fileExt, $allowed)) {
        echo "<h2>Error: Only JPG, JPEG, PNG, and GIF allowed.</h2>";
        exit();
    }

    // Validate size (2MB max)
    if ($fileSize > 2 * 1024 * 1024) {
        echo "<h2>Error: File size exceeds 2MB.</h2>";
        exit();
    }

    // Validate upload
    if ($fileError !== 0) {
        echo "<h2>Error uploading file.</h2>";
        exit();
    }

    // Unique file name
    $newFileName = "profile_" . $user_id . "_" . time() . "." . $fileExt;

    // Upload path
    $uploadPath = "uploads/" . $newFileName;

    // Move file
    if (move_uploaded_file($fileTmp, $uploadPath)) {

        // Save filename in database
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $newFileName, $user_id);

        if ($stmt->execute()) {
            echo "<h2>Upload successful!</h2>";
            echo "<img src='uploads/$newFileName' width='150' style='border-radius:8px;'><br><br>";
            echo "<a href='upload_form.php'>Upload another</a><br>";
            echo "<a href='dashboard.php'>Back to Dashboard</a>";
        } else {
            echo "<h2>Database update failed.</h2>";
        }

        $stmt->close();

    } else {
        echo "<h2>Failed to move uploaded file.</h2>";
    }

    $conn->close();

} else {
    echo "<h2>No file uploaded.</h2>";
}
?>
