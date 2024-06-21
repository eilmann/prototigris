<?php
include('./includes/db.php');
include('./includes/session.php');

function getParticipantData($participantID) {
    global $conn;
    $sql = "SELECT * FROM participants WHERE participantID='$participantID'";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

// Fetch tournament and form details based on formID
function getTournamentDetails($formID) {
    global $conn;
    $sql = "SELECT t.tournamentStartDate, t.tournamentEndDate, f.formTitle 
            FROM tournaments t 
            JOIN forms f ON t.formID = f.formID 
            WHERE t.formID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $formID);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fetch bracket data from the database based on formID
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['formID'])) {
    $formID = $_GET['formID'];
    $tournamentDetails = getTournamentDetails($formID);

    // Format the tournament start and end dates
    $formattedStartDate = formatDate($tournamentDetails['tournamentStartDate']);
    $formattedEndDate = formatDate($tournamentDetails['tournamentEndDate']);

    // Display tournament title and dates
    echo "<div id='bracket-container'>";
    echo "<h1 style='margin-top: 20px;'>{$tournamentDetails['formTitle']}</h1>";
    echo "</div>";
    echo "<div id='bracket-container'>";
    echo "<h3>Tournament Dates: $formattedStartDate to $formattedEndDate</h3>";
    echo "</div>";
    
    // Fetch bracket data from the database
    $sql = "SELECT * FROM brackets WHERE formID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        echo "<p>Error fetching bracket data.</p>";
        exit();
    }
    $stmt->bind_param("s", $formID);
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        echo "<p>Error fetching bracket data.</p>";
        exit();
    }
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Failed to get result: " . $stmt->error);
        echo "<p>Error fetching bracket data.</p>";
        exit();
    }

    // Initialize the global match counter
    global $matchCounter;
    $matchCounter = 0;

    // Display each bracket
    while ($row = $result->fetch_assoc()) {
        $bracketData = $row;
        $bracketID = $bracketData['bracketID'];

        // Display bracket based on elimination type
        if ($bracketData['eliminationType'] === "single") {
            // Display single elimination bracket
            displaySingleEliminationBracket($bracketData);
        } elseif ($bracketData['eliminationType'] === "double") {
            // Display double elimination bracket
            displayDoubleEliminationBracket($bracketData);
        }

        // Fetch and display schedule for the bracket
        displaySchedule($conn, $bracketID);
    }
}

function displaySingleEliminationBracket($bracketData) {
    // Generate and display the single elimination bracket HTML
    echo '<div id="bracket-container">';
    echo '<h2>Single Elimination Bracket</h2>';
    
    // Retrieve the bracket rounds from the data
    $rounds = json_decode($bracketData['bracket'], true);

    // Loop through each round and display the matches
    foreach ($rounds as $roundIndex => $round) {
        echo '<div class="round">';
        echo '<h3>Round ' . ($roundIndex + 1) . '</h3>';

        // Loop through each match in the round
        foreach ($round as $matchIndex => $match) {
            displayMatch($roundIndex, $match);
        }

        echo '</div>'; // Close round div
    }

    echo '</div>'; // Close bracket div
}

function displayDoubleEliminationBracket($bracketData) {
    echo '<div id="bracket-container">';
    echo '<h2>Double Elimination Bracket</h2>';

    // Retrieve the winner bracket, loser bracket, and finals from the data
    $bracketDataArray = json_decode($bracketData['bracket'], true);
    $winnerBracket = $bracketDataArray['winnerBracket'];
    $loserBracket = $bracketDataArray['loserBracket'];
    $finals = $bracketDataArray['finals'];

    // Display the winner bracket
    echo '<div>';
    echo '<h2>Winner Bracket</h2>';
    displayBracketRounds($winnerBracket);
    echo '</div>';

    // Display the loser bracket
    echo '<div>';
    echo '<h2>Loser Bracket</h2>';
    displayBracketRounds($loserBracket);
    echo '</div>';

    // Display the finals
    echo '<div>';
    echo '<h2>Finals</h2>';
    displayFinalMatch($finals[0]);
    echo '</div>';

    echo '</div>'; // Close bracket container
}

function displayBracketRounds($rounds) {
    foreach ($rounds as $roundIndex => $round) {
        echo '<div class="round">';
        echo '<h3>Round ' . ($roundIndex + 1) . '</h3>';

        foreach ($round as $match) {
            displayMatch($roundIndex, $match);
        }

        echo '</div>'; // Close round div
    }
}

function displayMatch($roundIndex, $match) {
    global $matchCounter;
    $matchLabel = chr(65 + $matchCounter);
    $team1 = isset($match[0]) ? $match[0] : "TBD";
    $team2 = isset($match[1]) ? $match[1] : "TBD";

    if ($roundIndex == 0 || isset($match['isFinal'])) {
        // Display match details for round 1 and final
        echo "<div class='match'><p>Match $matchLabel:<br> $team1 vs $team2</p></div>";
    } else {
        // Display only the match label for other rounds
        echo "<div class='match'><p>Match $matchLabel</p></div>";
    }

    $matchCounter++;
}

function displayFinalMatch($finalMatch) {
    displayMatch(0, $finalMatch);
}

function displaySchedule($conn, $bracketID) {
    // Fetch schedule data from the database based on bracketID
    $sql = "SELECT * FROM schedules WHERE bracketID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        echo "<p>Error fetching schedule data.</p>";
        exit();
    }
    $stmt->bind_param("s", $bracketID);
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        echo "<p>Error fetching schedule data.</p>";
        exit();
    }
    $result = $stmt->get_result();
    if (!$result) {
        error_log("Failed to get result: " . $stmt->error);
        echo "<p>Error fetching schedule data.</p>";
        exit();
    }

    // Display schedule
    echo '<div class="schedule" id="bracket-container">';
    echo '<h2>Schedule</h2>';
    echo '</div>';
    echo '<div class="schedule" id="bracket-container">';
    while ($row = $result->fetch_assoc()) {
        $formattedDate = formatDate($row['match_date']);
        echo '<div class="schedule-entry">';
        echo '<p> <b>' . $row['match_label'] . '</b> - ' . $formattedDate . ' ' . $row['match_time'] . '</p>';
        echo '</div>';
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./img/tigris_logo.png" type="icon">
    <title>View Bracket</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="./bracket_styles.css">
    <!-- Add any additional stylesheets here -->
</head>
<body style="background-color: #ccc;">

<header>
      <nav class="navbar">
        <div class="logo">
            <a href="index.php"><img src="./img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
        <input type="checkbox" id="menu-toggler">
        <label for="menu-toggler" id="hamburger-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24px" height="24px">
            <path d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 18h18v-2H3v2zm0-5h18V11H3v2zm0-7v2h18V6H3z"/>
          </svg>
        </label>
        <ul class="all-links">
            <li><a href="./index.php#home">Home</a></li>
          <li><a href="./index.php#join_tournament">Join Tournament</a></li>
          <li><a href="./index.php#fixture">Fixture</a></li>
          <li><a href="./index.php#result">Result</a></li>
          <li><a href="./index.php#about">About Us</a></li>
          <li>
                <?php
                // Check if a participant is logged in
                if (isParticipantLoggedIn()) {
                    $participantID = $_SESSION['participantID'];
                    $participantData = getParticipantData($participantID);
                    ?>
                    <a class="profile-link" href="./participant/view_profile.php" style="background-image: url('<?php echo $participantData['participantPic']; ?>')"></a>
                    <?php
                } else {
                    ?>
                    <a class="login-button" href="./participant/login.php">Login</a>
                    <?php
                }
                ?>
            </li>
            <?php
            // Show logout button if participant is logged in
            if (isParticipantLoggedIn()) {
                ?>
                <li><a class="login-button" href="./participant/logout.php">Logout</a></li>
                <?php
            }
            ?>
        </ul>
      </nav>
    </header>

<section id="bracket-section">
    <!-- Bracket display will be generated dynamically by PHP -->
    <!-- PHP logic for fetching and displaying bracket data goes here -->
</section>

<footer>
    <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="dashboard.php">Home</a>
        </span>
    </div>
</footer>

</body>
</html>
