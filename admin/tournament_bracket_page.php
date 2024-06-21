<?php
include('../includes/db.php');
include('../includes/session.php');

// Function to get all tournaments
function getTournaments($conn) {
    $sql = "SELECT forms.formID, forms.formTitle, tournaments.tournamentStartDate, tournaments.tournamentEndDate 
            FROM forms 
            INNER JOIN tournaments ON forms.tournamentID = tournaments.tournamentID";
    $result = $conn->query($sql);

    if (!$result) {
        error_log("Failed to fetch tournaments: " . $conn->error);
        return [];
    }

    $tournaments = [];
    while ($row = $result->fetch_assoc()) {
        $tournaments[] = $row;
    }
    return $tournaments;
}

$tournaments = getTournaments($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Tournament Bracket and Schedule Generator</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../bracket_styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        header {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 5;
        width: 100%;
        display: flex;
        justify-content: center;
        background: #333;
        }

        .navbar {
        display: flex;
        padding: 0 10px;
        max-width: 1200px;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        height: 80px;
        }

        .navbar input#menu-toggler {
        display: none;
        }

        .navbar #hamburger-btn {
        cursor: pointer;
        display: none;
        }

        .navbar .all-links {
        display: flex;
        align-items: center;
        }

        .navbar .all-links li {
        position: relative;
        list-style: none;
        }

        .navbar .logo a {
        display: flex;
        align-items: center;
        margin-left: 0;
        }

        .logo img {
            max-height: 40px; /* Adjust the height of the logo as needed */
            margin-right: 10px;
            margin-left: 20px;
        }

        header a, footer a {
        margin-left: 40px;
        text-decoration: none;
        color: #fff;
        height: 100%;
        padding: 20px 0;
        display: inline-block;
        }

        header a:hover, footer a:hover {
        color: #ddd;
        }

        .navbar .all-links .profile-link {
        width: 40px;
        height: 40px;
        border-radius: 50%; /* Make it a circle */
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        margin-top: auto; /* Add margin to the top */
        margin-bottom: auto; /* Add margin to the bottom */
        }

        nav a.login-button {
        color: white;
        background-color: #ff7300;
        text-decoration: none;
        padding: 5px 20px;
        border: 1px solid white;
        border-radius: 10px;
        }

        nav a.login-button:hover {
        background-color: #555;
        }

        footer {
        margin-top: auto;
        width: 100%;
        display: flex;
        justify-content: center;
        background: #000;
        padding: 20px 0;
        }

        footer div {
        padding: 0 10px;
        max-width: 1200px;
        width: 100%;
        display: flex;
        justify-content: space-between;
        }

        footer span {
        color: #fff;
        }

        footer a {
        padding: 0;
        }
    </style>
</head>
<body style="background-color: #ccc">

    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="dashboard.php"><img src="../img/tigris_logo.png" alt="logo">Administrator Dashboard</a>
        </div>
      </nav>
    </header>
    
    <div class="container mt-5">
        <section id="bracket-section" class="text-center">
            <h1>Bracket & Schedule Generator</h1>
            <form id="tournament-form" class="form-inline justify-content-center">
                <div class="form-group mx-sm-3 mb-2">
                    <label for="tournament-select" class="sr-only">Select Tournament</label>
                    <select id="tournament-select" name="formID" class="form-control" onchange="updateTournamentDates()">
                        <?php foreach ($tournaments as $tournament): ?>
                            <option value="<?php echo $tournament['formID']; ?>" 
                                    data-start-date="<?php echo $tournament['tournamentStartDate']; ?>" 
                                    data-end-date="<?php echo $tournament['tournamentEndDate']; ?>">
                                <?php echo $tournament['formTitle']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <label class="mr-2">Elimination Type:</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="elimination-type" value="single" checked>
                        <label class="form-check-label">Single</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="elimination-type" value="double">
                        <label class="form-check-label">Double</label>
                    </div>
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <label for="start-time" class="sr-only">Start Time</label>
                    <input type="time" id="start-time" name="startTime" class="form-control" required>
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <label for="end-time" class="sr-only">End Time</label>
                    <input type="time" id="end-time" name="endTime" class="form-control" required>
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <label for="match-duration" class="sr-only">Match Duration</label>
                    <input type="number" id="match-duration" name="matchDuration" class="form-control" placeholder="Match Duration (minutes)" required>
                </div>

                <button type="button" id="generate-button" class="btn btn-primary mb-2">Generate</button>
                <button type="button" id="publish-button" class="btn btn-success mb-2" style="display: none;">Publish Bracket</button>
            </form>

            <div id="tournament-dates" class="mt-3">
                <p>Start Date: <span id="tournament-start-date"></span></p>
                <p>End Date: <span id="tournament-end-date"></span></p>
            </div>
        </section>
        
        <div id="message-container"></div>
        <div id="bracket-container" class="mt-5"></div>
    </div>

    <footer>
      <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="#">Home</a>
        </span>
      </div>
    </footer>

    <script>
        function updateTournamentDates() {
            const select = document.getElementById('tournament-select');
            const selectedOption = select.options[select.selectedIndex];
            const startDate = selectedOption.getAttribute('data-start-date');
            const endDate = selectedOption.getAttribute('data-end-date');

            document.getElementById('tournament-start-date').textContent = startDate;
            document.getElementById('tournament-end-date').textContent = endDate;
        }

        document.getElementById('generate-button').addEventListener('click', function() {
            const formID = document.getElementById('tournament-select').value;
            const eliminationType = document.querySelector('input[name="elimination-type"]:checked').value;
            const startTime = document.getElementById('start-time').value;
            const endTime = document.getElementById('end-time').value;
            const matchDuration = document.getElementById('match-duration').value;

            fetch('generate_bracket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `formID=${formID}&eliminationType=${eliminationType}&startTime=${startTime}&endTime=${endTime}&matchDuration=${matchDuration}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    displayMessage(data.error);
                } else {
                    displayBracket(data.bracket);
                    displaySchedule(data.schedule);
                    document.getElementById('publish-button').style.display = 'inline-block';
                    window.generatedBracket = data; // Store the generated bracket in a global variable
                }
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('An error occurred while generating the bracket. Please check the console for details.');
            });
        });

        document.getElementById('publish-button').addEventListener('click', function() {
            if (!window.generatedBracket) {
                alert('Generate the bracket first.');
                return;
            }

            fetch('publish_bracket.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(window.generatedBracket)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    displayMessage(data.error);
                } else {
                    alert('Bracket and schedule published successfully!');
                    document.getElementById('publish-button').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        function displayMessage(message) {
            const container = document.getElementById('message-container');
            container.innerHTML = `<p style="color: red;">${message}</p>`;
        }

        function displayBracket(bracket) {
            const container = document.getElementById('bracket-container');
            container.innerHTML = ''; // Clear previous content

            if (bracket.winnerBracket) {
                displayDoubleEliminationBracket(bracket);
            } else {
                displaySingleEliminationBracket(bracket);
            }
        }

        function displaySingleEliminationBracket(bracket) {
            const container = document.getElementById('bracket-container');
            let matchCounter = 0;
            bracket.forEach((round, roundIndex) => {
                const roundDiv = document.createElement('div');
                roundDiv.classList.add('round');
                roundDiv.innerHTML = `<h3>Round ${roundIndex + 1}</h3>`;
                round.forEach((match, matchIndex) => {
                    const matchDiv = document.createElement('div');
                    matchDiv.classList.add('match');
                    if (roundIndex === 0) {
                        matchDiv.innerHTML = `<p>Match ${String.fromCharCode(65 + matchIndex)}:<br> ${match[0]} vs ${match[1]}</p>`;
                    } else {
                        const prevRoundMatches = round.length * 2; // Number of matches in the previous round
                        const previousMatch1 = Math.floor(matchCounter / 2) * 2;
                        const previousMatch2 = previousMatch1 + 1;
                        matchDiv.innerHTML = `<p>Match ${String.fromCharCode(65 + matchCounter)}</p>`;
                    }
                    matchCounter++;
                    roundDiv.appendChild(matchDiv);
                });
                container.appendChild(roundDiv);
            });
        }

        function displayDoubleEliminationBracket(bracket) {
            const container = document.getElementById('bracket-container');
            container.innerHTML = ''; // Clear previous content

            if (!bracket.winnerBracket || !bracket.loserBracket || !bracket.finals) {
                displayMessage('Invalid bracket data for double elimination.');
                return;
            }

            let matchCounter = 0;

            const winnerBracketDiv = document.createElement('div');
            winnerBracketDiv.innerHTML = '<h2>Winner Bracket</h2>';
            bracket.winnerBracket.forEach((round, roundIndex) => {
                const roundDiv = document.createElement('div');
                roundDiv.classList.add('round');
                roundDiv.innerHTML = `<h3>Round ${roundIndex + 1}</h3>`;
                round.forEach((match) => {
                    const matchDiv = document.createElement('div');
                    matchDiv.classList.add('match');
                    if (roundIndex === 0) {
                        matchDiv.innerHTML = `<p>Match ${String.fromCharCode(65 + matchCounter)}:<br> ${match[0]} vs ${match[1]}</p>`;
                    } else {
                        matchDiv.innerHTML = `<p>Match ${String.fromCharCode(65 + matchCounter)}</p>`;
                    }
                    matchCounter++;
                    roundDiv.appendChild(matchDiv);
                });
                winnerBracketDiv.appendChild(roundDiv);
            });
            container.appendChild(winnerBracketDiv);

            const loserBracketDiv = document.createElement('div');
            loserBracketDiv.innerHTML = '<h2>Loser Bracket</h2>';
            bracket.loserBracket.forEach((round, roundIndex) => {
                const roundDiv = document.createElement('div');
                roundDiv.classList.add('round');
                roundDiv.innerHTML = `<h3>Round ${roundIndex + 1}</h3>`;
                round.forEach(() => {
                    const matchDiv = document.createElement('div');
                    matchDiv.classList.add('match');
                    matchDiv.innerHTML = `<p>Match ${String.fromCharCode(65 + matchCounter)}</p>`;
                    matchCounter++;
                    roundDiv.appendChild(matchDiv);
                });
                loserBracketDiv.appendChild(roundDiv);
            });
            container.appendChild(loserBracketDiv);

            // Display the final match
            const finalsDiv = document.createElement('div');
            finalsDiv.innerHTML = '<h2>Finals</h2>';
            const finalMatch = bracket.finals[0];
            const finalMatchDiv = document.createElement('div');
            finalMatchDiv.classList.add('match');
            finalMatchDiv.innerHTML = `<p>Match ${String.fromCharCode(65 + matchCounter)}:<br> ${finalMatch[0]} vs ${finalMatch[1]}</p>`;
            finalsDiv.appendChild(finalMatchDiv);
            container.appendChild(finalsDiv);
        }

        function displaySchedule(schedule) {
            const container = document.getElementById('bracket-container');
            const scheduleDiv = document.createElement('div');
            scheduleDiv.classList.add('schedule');
            scheduleDiv.innerHTML = '<h2>Schedule</h2>';
            schedule.forEach((entry, index) => {
                const entryDiv = document.createElement('div');
                entryDiv.classList.add('schedule-entry');
                entryDiv.innerHTML = `<p>${entry.match} - ${entry.date} ${entry.time}</p>`;
                scheduleDiv.appendChild(entryDiv);
            });
            container.appendChild(scheduleDiv);
        }
    </script>
</body>
</html>


