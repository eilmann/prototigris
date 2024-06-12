<?php
include('../includes/db.php');
include('../includes/session.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTHM Tigris E-Sports Club Report</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .participant-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 150px;
        }
        .participant-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }
        .participant-name, .game-title {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="dashboard.php"><img src="../img/tigris_logo.png" alt="logo">Administrator Dashboard</a>
        </div>
            <ul class="all-links">
                <li><a class="login-button" href="logout.php">Logout</a></li>
            </ul>
      </nav>
    </header>
    <main>
    <section class="feed"> 
        <h1>Tournaments Report</h1> 
        <button onclick="window.print()" class="dashboard-button">Print/Save as PDF</button> 
    </section>

    <section id="admin-chart">
        <ul class="cards">
            <li>
                <h3>Total Tournaments:</h3>
                <canvas id="totalTournamentsChart"></canvas>
            </li>
            <li>
                <h3>Total Participants by Tournament:</h3>
                <canvas id="participantsPerTournamentChart"></canvas>
            </li>
            <li>
                <h3>Total Matches by Date:</h3>
                <canvas id="matchSchedulesChart"></canvas>
            </li>
            <li>
                <h3>Total Tournaments by Game Title:</h3>
                <canvas id="tournamentsByGameTitleChart"></canvas>
            </li>
        </ul>
    </section>
    <section class="feed"> <h1>Participants Report</h1> </section>
    <section id="admin-chart">
        <ul class="cards">
            <li>
                <h3>Most Active Participants:</h3>
                <canvas id="mostActiveParticipantsChart"></canvas>
            </li>
            <li>
                <h3>Top Participants:</h3>
                <canvas id="topParticipantsChart"></canvas>
            </li>
            <li>
                <h3>Top Participants by Game Title:</h3>
                <div id="topParticipantsByGameContainer" style="display:flex; flex-direction:row;"></div>
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
    <script src="chart_functions.js"></script>
</body>
</html>
