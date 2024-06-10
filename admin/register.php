<?php
include('../includes/db.php');
include('../includes/session.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $adminID = $_POST['adminID'];
    $adminName = $_POST['adminName'];
    $adminEmail = $_POST['adminEmail'];
    $adminPW = $_POST['adminPW'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($adminPW !== $confirmPassword) {
        echo '<script>alert("Passwords do not match"); window.history.back();</script>';
        exit();
    }

    // Check if adminID already exists
    $sql = "SELECT adminID FROM admins WHERE adminID = '$adminID'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<script>alert("Admin ID already exists"); window.history.back();</script>';
        exit();
    }

    // Check if adminEmail already exists
    $sql = "SELECT adminEmail FROM admins WHERE adminEmail = '$adminEmail'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<script>alert("Email already exists"); window.history.back();</script>';
        exit();
    }

    // Handle profile picture upload
    $targetDir = "../uploads/";
    $targetFile = $targetDir . basename($_FILES["adminPic"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["adminPic"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo '<script>alert("File is not an image."); window.history.back();</script>';
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo '<script>alert("Sorry, file already exists."); window.history.back();</script>';
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["adminPic"]["size"] > 500000) {
        echo '<script>alert("Sorry, your file is too large."); window.history.back();</script>';
        $uploadOk = 0;
    }

    // Allow only certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo '<script>alert("Sorry, only JPG, JPEG, PNG & GIF files are allowed."); window.history.back();</script>';
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo '<script>alert("Sorry, your file was not uploaded."); window.history.back();</script>';
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($_FILES["adminPic"]["tmp_name"], $targetFile)) {

            // File uploaded successfully, continue with database insert
            $sql = "INSERT INTO admins (adminID, adminName, adminEmail, adminPic, adminPW) 
                    VALUES ('$adminID', '$adminName', '$adminEmail', '$targetFile', '$adminPW')";

            if ($conn->query($sql) === TRUE) {
                // Registration successful, redirect to login page
                echo '<script>alert("Registration successful!"); window.location.href = "dashboard.php";</script>';
                exit();
            } else {
                echo '<script>alert("Error: ' . $sql . '<br>' . $conn->error . '"); window.history.back();</script>';
            }
        } else {
            echo '<script>alert("Sorry, there was an error uploading your file."); window.history.back();</script>';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="../style.css">

    <script>
        function validateForm() {
            var password = document.getElementById("adminPW").value;
            var confirmPassword = document.getElementById("confirmPassword").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<header>
      <nav class="navbar">
        <div class="logo">
            <a href="dashboard.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
        <input type="checkbox" id="menu-toggler">
        <label for="menu-toggler" id="hamburger-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
          </svg>
        </label>
        <ul class="all-links">
          <li><a class="login-button" href="logout.php">Logout</a></li>
        </ul>
      </nav>
    </header>

<main>
    <form action="register.php" method="post" class="form" enctype="multipart/form-data" onsubmit="return validateForm()">
        <h3>Create Admin Account</h3>
        <label for="adminID">Admin ID:</label>
        <input type="text" name="adminID" required>

        <label for="adminName">Full Name:</label>
        <input type="text" name="adminName" required>

        <label for="adminEmail">Email:</label>
        <input type="email" name="adminEmail" required>

        <label for="adminPic">Profile Picture:</label>
        <input type="file" name="adminPic" accept="image/*" required>

        <label for="adminPW">Password:</label>
        <input type="password" name="adminPW" id="adminPW" required>

        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" name="confirmPassword" id="confirmPassword" required>

        <button type="submit">Register</button>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
</main>

<footer>
    <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="../client/index.php">Home</a>
        </span>
    </div>
</footer>

<script src="../js/script.js"></script>
</body>
</html>
