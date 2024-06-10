<?php
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $participantID = $_POST['participantID'];
    $participantName = $_POST['participantName'];
    $participantEmail = $_POST['participantEmail'];
    $participantPW = $_POST['participantPW'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($participantPW !== $confirmPassword) {
        echo '<script>alert("Passwords do not match"); window.history.back();</script>';
        exit();
    }

    // Check if participantID already exists
    $sql = "SELECT participantID FROM participants WHERE participantID = '$participantID'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<script>alert("Participant ID already exists"); window.history.back();</script>';
        exit();
    }

    // Check if participantEmail already exists
    $sql = "SELECT participantEmail FROM participants WHERE participantEmail = '$participantEmail'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<script>alert("Email already exists"); window.history.back();</script>';
        exit();
    }

    // Handle profile picture upload
    $targetDir = "../uploads/";
    $targetFile = $targetDir . basename($_FILES["participantPic"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["participantPic"]["tmp_name"]);
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
    if ($_FILES["participantPic"]["size"] > 500000) {
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
        if (move_uploaded_file($_FILES["participantPic"]["tmp_name"], $targetFile)) {
            // Hash the password before storing it in the database
            $hashedPassword = password_hash($participantPW, PASSWORD_DEFAULT);

            // File uploaded successfully, continue with database insert
            $sql = "INSERT INTO participants (participantID, participantName, participantEmail, participantPic, participantPW) 
                    VALUES ('$participantID', '$participantName', '$participantEmail', '$targetFile', '$hashedPassword')";

            if ($conn->query($sql) === TRUE) {
                // Registration successful, redirect to login page
                echo '<script>alert("Registration successful!"); window.location.href = "login.php";</script>';
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
    <title>Participant Registration</title>
    <link rel="stylesheet" href="../style.css">

    <script>
        function validateForm() {
            var password = document.getElementById("participantPW").value;
            var confirmPassword = document.getElementById("confirmPassword").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match");
                return false;
            }
            return true;
        }
    </script>
</head>
<body style="background-image: url(../img/tigris_background.jpg)">

<header>
    <nav class="navbar">
        <div class="logo">
            <a href="../client/index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
    </nav>
</header>

<main>
    <form action="register.php" method="post" class="form" enctype="multipart/form-data" onsubmit="return validateForm()">
        <h3>Create Participant Account</h3>
        <label for="participantID">Participant ID:</label>
        <input type="text" name="participantID" required>

        <label for="participantName">Full Name:</label>
        <input type="text" name="participantName" required>

        <label for="participantEmail">Email:</label>
        <input type="email" name="participantEmail" required>

        <label for="participantPic">Profile Picture:</label>
        <input type="file" name="participantPic" accept="image/*" required>

        <label for="participantPW">Password:</label>
        <input type="password" name="participantPW" id="participantPW" required>

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
