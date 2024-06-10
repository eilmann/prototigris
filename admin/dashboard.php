<?php
include('../includes/db.php');
include('../includes/session.php');

function getAdminData($adminID) {
    global $conn;
    $sql = "SELECT * FROM admins WHERE adminID='$adminID'";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
  }  

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Administrator Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="background: url(../img/tigris_background.jpg)">

    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="dashboard.php"><img src="../img/tigris_logo.png" alt="logo">Administrator Dashboard</a>
        </div>
            <ul class="all-links">
                <li>
                    <?php
                    // Check if a admin is logged in
                    if (isAdminLoggedIn()) {
                        $adminID = $_SESSION['adminID'];
                        $adminData = getAdminData($adminID);
                        ?>
                        <a class="profile-link" href="view_profile.php" style="background-image: url('<?php echo $adminData['adminPic']; ?>')"></a>
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

    <div>
    <section>
        <div>
        <?php

        $adminName = $_SESSION['adminName'];
        
        if (isAdminLoggedIn()) {
            echo '<h1 class="greeting-message">Hello, ' . $_SESSION['adminName'] . '!</h1>';
        }
        ?>

        </div>

        <div class="dashboard-buttons">
            <a href="manage_tournament.php" class="dashboard-button">Manage Tournament Post</a>
            <a href="manage_registration_form.php" class="dashboard-button">Manage Registration Form</a>
            <a href="tournament_bracket_page.php" class="dashboard-button">Bracket & Schedule Generator</a>
            <a href="manage_bracket.php" class="dashboard-button">Manage Bracket & Schedule</a>
            <a href="tournament_result.php" class="dashboard-button">Manage Tournament Result</a>
            <a href="report_page.php" class="dashboard-button">Generate Report</a>
        </div>
    </section>
    </div>

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
