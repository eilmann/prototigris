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

// Function to get list of tournaments that have ended
function getEndedTournaments() {
    global $conn;
    $currentDate = date("Y-m-d");
    $sql = "SELECT * FROM tournaments WHERE tournamentEndDate <= '$currentDate'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get list of participants for a specific tournament
function getParticipantsByTournament($tournamentID) {
    global $conn;
    $sql = "SELECT participants.participantID, participants.participantName 
            FROM participants 
            JOIN tournament_registrations ON participants.participantID = tournament_registrations.participantID 
            JOIN forms ON tournament_registrations.formID = forms.formID 
            WHERE forms.tournamentID = '$tournamentID'";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Check if an admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tournamentID = $_POST['tournamentID'];
    $firstPlaceID = $_POST['firstPlaceID'];
    $secondPlaceID = $_POST['secondPlaceID'];
    $thirdPlaceID = $_POST['thirdPlaceID'];

    // Update the result in the tournaments table
    $update_result_sql = "UPDATE tournaments SET firstPlaceID='$firstPlaceID', secondPlaceID='$secondPlaceID', thirdPlaceID='$thirdPlaceID' WHERE tournamentID='$tournamentID'";

    if ($conn->query($update_result_sql) === TRUE) {
        // Update participant performance for first place
        $update_first_place_sql = "INSERT INTO participant_performance (participantID, firstPlaceCount, totalParticipation) VALUES ('$firstPlaceID', 1, 1) ON DUPLICATE KEY UPDATE firstPlaceCount = firstPlaceCount + 1, totalParticipation = totalParticipation + 1";
        $conn->query($update_first_place_sql);

        // Update participant performance for second place
        $update_second_place_sql = "INSERT INTO participant_performance (participantID, secondPlaceCount, totalParticipation) VALUES ('$secondPlaceID', 1, 1) ON DUPLICATE KEY UPDATE secondPlaceCount = secondPlaceCount + 1, totalParticipation = totalParticipation + 1";
        $conn->query($update_second_place_sql);

        // Update participant performance for third place
        $update_third_place_sql = "INSERT INTO participant_performance (participantID, thirdPlaceCount, totalParticipation) VALUES ('$thirdPlaceID', 1, 1) ON DUPLICATE KEY UPDATE thirdPlaceCount = thirdPlaceCount + 1, totalParticipation = totalParticipation + 1";
        $conn->query($update_third_place_sql);

        echo '<script>
                alert("Winners announced successfully!");
                window.location.href = window.location.href;
              </script>';
    } else {
        echo '<script>alert("Error announcing winners: ' . $conn->error . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Announce Tournament Winners</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="background: url(../img/tigris_background.jpg)">

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

<main class="form" style="min-width: fit-content;">
    <h1 style="margin-bottom: 20px;">Announce Tournament Winners</h1>
    <form action="tournament_result.php" method="post">
        <label for="tournamentID">Tournament Name:</label><br>
        <select name="tournamentID" id="tournamentID" required onchange="fetchParticipants(this.value)">
            <option value="">Select a tournament</option>
            <?php
            $tournaments = getEndedTournaments();
            foreach ($tournaments as $tournament) {
                $date = DateTime::createFromFormat('Y-m-d', $tournament['tournamentEndDate']);
                $formattedDate = $date->format('d/m/Y');
                echo "<option value=\"{$tournament['tournamentID']}\">{$tournament['tournamentName']} (Ended on: {$formattedDate})</option> ";
            }
            ?>
        </select>

        <div id="participantsSelection" style="margin-top: 20px;">
            <label for="firstPlaceID">1st Place:</label>
            <select name="firstPlaceID" id="firstPlaceID" required>
                <option value="">Select a participant</option>
            </select> <br>

            <label for="secondPlaceID">2nd Place:</label>
            <select name="secondPlaceID" id="secondPlaceID" required>
                <option value="">Select a participant</option>
            </select> <br>

            <label for="thirdPlaceID">3rd Place:</label>
            <select name="thirdPlaceID" id="thirdPlaceID" required>
                <option value="">Select a participant</option>
            </select> <br>
        </div>

        <button type="submit">Submit</button>
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
<script>
function fetchParticipants(tournamentID) {
    if (tournamentID === "") {
        document.getElementById("firstPlaceID").innerHTML = "<option value=''>Select a participant</option>";
        document.getElementById("secondPlaceID").innerHTML = "<option value=''>Select a participant</option>";
        document.getElementById("thirdPlaceID").innerHTML = "<option value=''>Select a participant</option>";
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_participants.php?tournamentID=" + tournamentID, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var participants = JSON.parse(xhr.responseText);
            var firstPlaceSelect = document.getElementById("firstPlaceID");
            var secondPlaceSelect = document.getElementById("secondPlaceID");
            var thirdPlaceSelect = document.getElementById("thirdPlaceID");

            firstPlaceSelect.innerHTML = "<option value=''>Select a participant</option>";
            secondPlaceSelect.innerHTML = "<option value=''>Select a participant</option>";
            thirdPlaceSelect.innerHTML = "<option value=''>Select a participant</option>";

            participants.forEach(function(participant) {
                var option = document.createElement("option");
                option.value = participant.participantID;
                option.text = participant.participantName;

                firstPlaceSelect.add(option.cloneNode(true));
                secondPlaceSelect.add(option.cloneNode(true));
                thirdPlaceSelect.add(option.cloneNode(true));
            });
        }
    };
    xhr.send();
}
</script>
</body>
</html>
