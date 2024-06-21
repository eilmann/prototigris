<?php
include('../includes/db.php');
include('../includes/session.php');

$participantID = $_SESSION['participantID'];

// Retrieve participant information
$participantInfoSql = "SELECT participantName, participantPic FROM participants WHERE participantID = '$participantID'";
$participantInfoResult = $conn->query($participantInfoSql);

if ($participantInfoResult->num_rows > 0) {
    $participantInfo = $participantInfoResult->fetch_assoc();
    $participantName = $participantInfo['participantName'];
    $participantPic = $participantInfo['participantPic'];
} else {
    $participantName = 'Unknown';
    $participantPic = '../img/default_profile_pic.png'; // Default image if none found
}

// Retrieve participant performance data
$performanceSql = "SELECT * FROM participant_performance WHERE participantID = '$participantID'";
$performanceResult = $conn->query($performanceSql);

if ($performanceResult->num_rows > 0) {
    $performanceData = $performanceResult->fetch_assoc();
} else {
    $performanceData = [
        'totalParticipation' => 0,
        'firstPlaceCount' => 0,
        'secondPlaceCount' => 0,
        'thirdPlaceCount' => 0
    ];
}

// Retrieve game participation data
$gameParticipationSql = "SELECT tournaments.gameTitle, COUNT(*) as count 
                        FROM tournament_registrations 
                        JOIN forms ON tournament_registrations.formID = forms.formID 
                        JOIN tournaments ON forms.tournamentID = tournaments.tournamentID 
                        WHERE tournament_registrations.participantID = '$participantID'
                        GROUP BY tournaments.gameTitle";
$gameParticipationResult = $conn->query($gameParticipationSql);

$gameParticipationData = [];
while ($row = $gameParticipationResult->fetch_assoc()) {
    $gameParticipationData[] = $row;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Performance - Participant</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
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
            <li><a class="login-button" href="../participant/logout.php">Logout</a></li>
        </ul>
      </nav>
    </header>
    <main>
        <section class="feed" style="margin-bottom: 0px; padding-bottom: 0px">
            <h2>Your Performance</h2>
            <div class="participant-info">
                <img src="<?= $participantPic ?>" alt="<?= $participantName ?>" class="participant-pic">
                <h3><?= $participantName ?></h3>
            </div>
        </section>
        <section class="chart">
            <ul class="cards">
                <li>
                    <h3>Participation Statistic:</h3>
                    <canvas id="participationChart"></canvas>
                </li>
                <li>
                    <h3>Favourite Title:</h3>
                    <canvas id="gameParticipationChart"></canvas>
                </li>
            </ul>
        </section>
    </main>
    <footer>
      <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="#">Home</a>
        </span>
      </div>
    </footer>

    <script>
        // Bar chart for total participation and wins
        const ctxParticipation = document.getElementById('participationChart').getContext('2d');
        const participationData = {
            labels: ['Total Participation', '1st Place Wins', '2nd Place Wins', '3rd Place Wins'],
            datasets: [{
                label: 'Count',
                data: [<?= $performanceData['totalParticipation'] ?>, <?= $performanceData['firstPlaceCount'] ?>, <?= $performanceData['secondPlaceCount'] ?>, <?= $performanceData['thirdPlaceCount'] ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        const participationChart = new Chart(ctxParticipation, {
            type: 'bar',
            data: participationData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pie chart for game title participation
        const ctxGameParticipation = document.getElementById('gameParticipationChart').getContext('2d');
        const gameParticipationData = <?= json_encode($gameParticipationData) ?>;
        const gameTitles = gameParticipationData.map(data => data.gameTitle);
        const gameCounts = gameParticipationData.map(data => data.count);

        const gameParticipationChart = new Chart(ctxGameParticipation, {
            type: 'pie',
            data: {
                labels: gameTitles,
                datasets: [{
                    label: 'Game Participation',
                    data: gameCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html>
