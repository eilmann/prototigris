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

// Check if a admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$adminID = $_SESSION['adminID'];
$adminData = getAdminData($adminID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Admin Profile</title>
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
                // Check if a admin is logged in
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

    <main class="form" style="min-width:500px;">
        <h1 style="margin-bottom: 20px; margin-top: 15px; margin-left: 20px;">Administrator Profile</h1>
        <p style="margin-left: 20px;"><strong>Admin ID:</strong> <?php echo $adminData['adminID']; ?></p>
        <p style="margin-left: 20px;"><strong>Name:</strong> <?php echo $adminData['adminName']; ?></p>
        <p style="margin-left: 20px;"><strong>Email:</strong> <?php echo $adminData['adminEmail']; ?></p>
        <p style="margin-left: 20px;"><strong>Profile Picture:</strong> <br> <img class="profile-page" src="<?php echo $adminData['adminPic']; ?>" 
        alt="Profile Picture" style="height: auto; width: 300px; margin-bottom: 20px;"></p>

        <div style="margin-bottom: 20px;">
            <a class="dashboard-button" href="edit_profile.php" style="padding-left:20px; padding-right:20px; margin-left:20px;">Edit Profile</a>
            <a class="dashboard-button" href="register.php" style="padding-left:20px; padding-right:20px; margin-left:5px;">Register New Admin</a>
        </div>
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

