<?php
include('../includes/db.php');
include('../includes/session.php');

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

// Function to get all admin data
function getAllAdmins() {
    global $conn;
    $sql = "SELECT adminID, adminName, adminEmail, adminPic FROM admins";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result : null;
}

$admins = getAllAdmins();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Admin List</title>
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

    <section>
        <h1 style="color:white;">Admin List</h1>
        <?php if ($admins): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Profile Picture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($admin = $admins->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $admin['adminID']; ?></td>
                            <td><?php echo $admin['adminName']; ?></td>
                            <td><?php echo $admin['adminEmail']; ?></td>
                            <td><img src="<?php echo $admin['adminPic']; ?>" alt="Profile Picture" style="height: 100px; width: auto;"></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No admins found.</p>
        <?php endif; ?>
    </section>

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
