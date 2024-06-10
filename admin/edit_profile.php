<?php
include('../includes/db.php');
include('../includes/session.php');

// Function to get admin data by adminID
function getAdminData($adminID) {
    global $conn;
    $sql = "SELECT * FROM admins WHERE adminID='$adminID'";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

// Check if an admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$adminID = $_SESSION['adminID'];
$adminData = getAdminData($adminID);

// Check if the form is submitted for profile update or delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Retrieve form data
        $adminName = $_POST['adminName'];
        $adminEmail = $_POST['adminEmail'];

        // Update admin data in the database
        $update_sql = "UPDATE admins SET adminName='$adminName', adminEmail='$adminEmail' WHERE adminID='$adminID'";
        
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
                        $update_profile_pic_sql = "UPDATE admins SET adminPic='$profilePicturePath' WHERE adminID='$adminID'";
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
    } elseif (isset($_POST['delete'])) {
        // Delete admin data from the database
        $delete_sql = "DELETE FROM admins WHERE adminID='$adminID'";
        
        if ($conn->query($delete_sql) === TRUE) {
            // Log out the admin and redirect to login page
            session_destroy();
            echo '<script>alert("Profile deleted successfully!"); window.location.href = "login.php";</script>';
        } else {
            echo '<script>alert("Error deleting profile: ' . $conn->error . '");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Edit Administrator Profile</title>
    <link rel="stylesheet" href="../style.css">
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
          <li>
                <?php
                // Check if an admin is logged in
                if (isAdminLoggedIn()) {
                    $adminID = $_SESSION['adminID'];
                    $adminData = getAdminData($adminID);
                    ?>
                    <a class="profile-link" href="../admin/view_profile.php" style="background-image: url('<?php echo $adminData['adminPic']; ?>')"></a>
                    <?php
                } else {
                    ?>
                    <a class="login-button" href="login.php">Login</a>
                    <?php
                }
                ?>
            </li>
            <?php
            // Show logout button if admin is logged in
            if (isAdminLoggedIn()) {
                ?>
                <li><a class="login-button" href="logout.php">Logout</a></li>
                <?php
            }
            ?>
        </ul>
      </nav>
    </header>

    <main class="form">
        <h1>Edit Administrator Profile</h1>
        <!-- Create a form for editing profile information -->
        <form action="edit_profile.php" method="post" enctype="multipart/form-data">
            <label for="adminName">Name:</label>
            <input type="text" name="adminName" value="<?php echo $adminData['adminName']; ?>" required>

            <label for="adminEmail">Email:</label>
            <input type="email" name="adminEmail" value="<?php echo $adminData['adminEmail']; ?>" required>

            <label for="profilePicture">Profile Picture:</label><br>
            <!-- Display current profile picture -->
            <img class="profile-page-preview" src="<?php echo $adminData['adminPic']; ?>" alt="Current Profile Picture" style="height: 300px; width: auto;">
            
            <input type="file" name="profilePicture">

            <!-- Add other fields as needed -->

            <button type="submit" name="update">Save Changes</button>
            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete your profile?');">Delete Profile</button>
        </form>
    </main>

    <footer>
      <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="dashboard.php">Home</a>
        </span>
      </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
