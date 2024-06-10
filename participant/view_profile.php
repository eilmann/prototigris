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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Participant Profile</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="../client/index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
        <input type="checkbox" id="menu-toggler">
        <label for="menu-toggler" id="hamburger-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
          </svg>
        </label>
        <ul class="all-links">
            <li><a href="../client/index.php#home">Home</a></li>
            <li><a href="../client/index.php#join_tournament">Join Tournament</a></li>
            <li><a href="../client/index.php#fixture">Fixture</a></li>
            <li><a href="../client/index.php#result">Result</a></li>
            <li><a href="../client/index.php#about">About Us</a></li>
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
        <h1 style="margin-bottom: 20px; margin-top: 15px; margin-left: 20px;">Player Profile</h1>
        <p style="margin-left: 20px;"><strong>Participant ID:</strong> <?php echo $participantData['participantID']; ?></p>
        <p style="margin-left: 20px;"><strong>Name:</strong> <?php echo $participantData['participantName']; ?></p>
        <p style="margin-left: 20px;"><strong>Email:</strong> <?php echo $participantData['participantEmail']; ?></p>
        <p style="margin-left: 20px;"><strong>Profile Picture:</strong> <br> <img class="profile-page" src="<?php echo $participantData['participantPic']; ?>
        " alt="Profile Picture" style="height: auto; width: 300px; margin-bottom: 20px;"></p>
        <!-- Add other fields as needed -->

        <div style="margin-bottom: 20px;">
            <a class="dashboard-button" href="edit_profile.php" style="padding-left:20px; padding-right:20px; margin-left:20px;">Edit Profile</a>
            <a class="dashboard-button" href="participant_performance.php" style="padding-left:20px; padding-right:20px; margin-left:0px;">View Performance</a>
        </div>
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
