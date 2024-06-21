<?php
include('../includes/db.php');
include('../includes/session.php');

// Function to get participant data by participantID
function getParticipantData($participantID) {
    global $conn;
    $sql = "SELECT * FROM participants WHERE participantID='$participantID'";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

// Check if a participant is logged in
if (!isParticipantLoggedIn()) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$participantID = $_SESSION['participantID'];
$participantData = getParticipantData($participantID);

// Check if the form is submitted for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $participantName = $_POST['participantName'];
    $participantEmail = $_POST['participantEmail'];

    // Update participant data in the database
    $update_sql = "UPDATE participants SET participantName='$participantName', participantEmail='$participantEmail' WHERE participantID='$participantID'";
    
    if ($conn->query($update_sql) === TRUE) {
        // Check if a new profile picture is uploaded
        if ($_FILES['profilePicture']['error'] == 0) {
            // Handle profile picture upload
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["profilePicture"]["name"]);
            $uploadOk = 1;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["profilePicture"]["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["profilePicture"]["size"] > 500000) {
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Sorry, your file was not uploaded.";
            } else {
                // If everything is ok, try to upload file
                if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $target_file)) {
                    // Update the profile picture path in the database
                    $profilePicturePath = "../uploads/" . basename($_FILES["profilePicture"]["name"]);
                    $update_profile_pic_sql = "UPDATE participants SET participantPic='$profilePicturePath' WHERE participantID='$participantID'";
                    $conn->query($update_profile_pic_sql);
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }

        echo '<script>alert("Profile updated successfully!"); window.location.href = "view_profile.php";</script>';
    } else {
        echo '<script>alert("Error updating profile: ' . $conn->error . '");</script>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Edit Participant Profile</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="../index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
        <input type="checkbox" id="menu-toggler">
        <label for="menu-toggler" id="hamburger-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
          </svg>
        </label>
        <ul class="all-links">
          <li><a href="../index.php#home">Home</a></li>
          <li><a href="../index.php#join_tournament">Join Tournament</a></li>
          <li><a href="../index.php#fixture">Fixture</a></li>
          <li><a href="../index.php#result">Result</a></li>
          <li><a href="../index.php#about">About Us</a></li>
          <li>
                <?php
                // Check if a participant is logged in
                if (isParticipantLoggedIn()) {
                    $participantID = $_SESSION['participantID'];
                    $participantData = getParticipantData($participantID);
                    ?>
                    <a class="profile-link" href="../participant/view_profile.php" style="background-image: url('<?php echo $participantData['participantPic']; ?>')"></a>
                    <?php
                } else {
                    ?>
                    <a class="login-button" href="login.php">Login</a>
                    <?php
                }
                ?>
            </li>
            <?php
            // Show logout button if participant is logged in
            if (isParticipantLoggedIn()) {
                ?>
                <li><a class="login-button" href="logout.php">Logout</a></li>
                <?php
            }
            ?>
        </ul>
      </nav>
    </header>

    <main class="form" style="min-width:500px;">
        <h1 style="margin-bottom: 20px;">Edit Player Profile</h1>
        <!-- Create a form for editing profile information -->
        <form action="edit_profile.php" method="post" enctype="multipart/form-data">
            <label for="participantName">Name:</label>
            <input type="text" name="participantName" value="<?php echo $participantData['participantName']; ?>" required>

            <label for="participantEmail">Email:</label>
            <input type="email" name="participantEmail" value="<?php echo $participantData['participantEmail']; ?>" required>

            <label for="profilePicture">Profile Picture:</label><br>
            <!-- Display current profile picture -->
            <img class="profile-page-preview" src="<?php echo $participantData['participantPic']; ?>" alt="Current Profile Picture" 
            style="height: auto; width: 300px; margin-bottom: 20px;">
            
            <input type="file" name="profilePicture" accept="image/*">

            <button type="submit">Save Changes</button>
        </form>
    </main>

    <footer>
      <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="#">Home</a>
        </span>
      </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
