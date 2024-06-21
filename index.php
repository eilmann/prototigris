<?php
include('./includes/db.php');
include('./includes/session.php');

function getParticipantData($participantID) {
  global $conn;
  $sql = "SELECT * FROM participants WHERE participantID='$participantID'";
  $result = $conn->query($sql);
  return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

?>
<!DOCTYPE html>

<html lang="en">
    
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" href="./img/tigris_logo.png" type="icon">
    <title>UTHM Tigris E-Sports Website</title>
    
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
  </head>

  <body>
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
          <li><a href="#home">Home</a></li>
          <li><a href="#join_tournament">Join Tournament</a></li>
          <li><a href="#fixture">Fixture</a></li>
          <li><a href="#result">Result</a></li>
          <li><a href="#about">About Us</a></li>
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

    <section class="homepage" id="home">
        <div class="background-video">
            <video autoplay loop muted>
              <source src="./img/tigris_vid.mp4" type="video/mp4">
              Your browser does not support the video tag.
            </video>
        </div>
      <div class="content">
        <div class="text">
          <h1>Welcome To Our Official Website</h1>
          <p>Sign Up and show your talent now!</p>
      </div>
        <a href="#join_tournament">Join Now</a>
      </div>
    </section>

    <!--Join Tournament-->
    <section class="feed" id="join_tournament">
      <h2>Join Tournament</h2>
      <p>Explore active tournament and participate to win exciting prizes.</p>

      <?php

      // Function to get tournament status
      function getTournamentStatus($startDate, $endDate) {
          $currentDate = date("Y-m-d");
          if ($currentDate < $startDate) {
              return "upcoming";
          } elseif ($currentDate <= $endDate) {
              return "ongoing";
          } else {
              return "ended";
          }
      }

      // Query to retrieve tournament data
      $sql = "SELECT * FROM tournaments ORDER BY tournamentStartDate DESC";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          echo '<ul class="cards">';
          while ($row = $result->fetch_assoc()) {
              echo '<li class="card">';
              echo '<h3>' . $row['tournamentName'] . '</h3>';
              echo '<p>' . $row['postDesc'] . '</p>';
              echo '<p>' . date('d/m/Y', strtotime($row['tournamentStartDate'])) . ' - ' . date('d/m/Y', strtotime($row['tournamentEndDate'])) . '</p>';
              if (!empty($row['postPic'])) {
                  echo '<img src="' . $row['postPic'] . '" alt="Tournament Image"><br>';
              }

              // Check tournament status
              $status = getTournamentStatus($row['tournamentStartDate'], $row['tournamentEndDate']);
              if ($status == "upcoming") {
                  echo '<a href="./participant/tournament_registration.php?formID=' . $row['formID'] . '">Join Tournament</a>';
              } else {
                  echo '<a href="" class="isDisabled">Registration Closed</a>';
              }

              // Add other fields as needed
              echo '</li>';
          }
          echo '</ul>'; // Close the container
      } else {
          echo "No active tournaments found.";
      }

      ?>
    </section>

    <!--Bracket and Schedule-->
    <section class="fixture" id="fixture">
        <h2>Fixture</h2>
        <p>View tournament bracket and schedule.</p>
        <ul class="cards">
            <?php
            // Query to retrieve tournament data
            $sql = "SELECT forms.formID, forms.formTitle, tournaments.tournamentStartDate, tournaments.tournamentEndDate, brackets.eliminationType, brackets.bracketID, COUNT(schedules.scheduleID) AS totalMatches 
                    FROM brackets 
                    LEFT JOIN forms ON brackets.formID = forms.formID 
                    LEFT JOIN tournaments ON forms.tournamentID = tournaments.tournamentID 
                    LEFT JOIN schedules ON brackets.bracketID = schedules.bracketID 
                    GROUP BY forms.formTitle, tournaments.tournamentStartDate, tournaments.tournamentEndDate, brackets.eliminationType, brackets.bracketID";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<li class="card">';
                    echo '<h3>' . $row['formTitle'] . '</h3>';
                    echo '<p>Start: ' . date('d/m/Y', strtotime($row['tournamentStartDate'])) . ' End: ' . date('d/m/Y', strtotime($row['tournamentEndDate'])) . '</p>';
                    echo '<p>Elimination Type: ' . ucfirst($row['eliminationType']) . '</p>';
                    echo '<p>Total Matches: ' . $row['totalMatches'] . '</p>';
                    echo '<a href="view_bracket.php?formID=' . $row['formID'] . '&bracketID=' . $row['bracketID'] . '">View Bracket</a>';
                    echo '</li>';
                }
            } else {
                echo "No active tournaments found.";
            }
            ?>
        </ul>
    </section>



    <!-- Result Section -->
    <section class="result" id="result">
        <h2>Result</h2>
        <p>Update on previous tournament result.</p>
        <ul class="cards">
            <?php

            // Query to retrieve tournament results for ended tournaments
            $result_sql = "SELECT tournaments.tournamentID, tournaments.tournamentName, tournaments.tournamentStartDate, tournaments.tournamentEndDate, 
                                participants.participantName AS firstPlaceName, participants.participantPic AS firstPlacePic,
                                secondPlace.participantName AS secondPlaceName, thirdPlace.participantName AS thirdPlaceName
                            FROM tournaments
                            LEFT JOIN participants ON tournaments.firstPlaceID = participants.participantID
                            LEFT JOIN participants AS secondPlace ON tournaments.secondPlaceID = secondPlace.participantID
                            LEFT JOIN participants AS thirdPlace ON tournaments.thirdPlaceID = thirdPlace.participantID
                            WHERE tournaments.tournamentEndDate < CURDATE()";

            $result_result = $conn->query($result_sql);

            if ($result_result->num_rows > 0) {
                while ($row = $result_result->fetch_assoc()) {
                    echo '<li class="card">';
                    echo '<img src="' . $row['firstPlacePic'] . '" alt="' . $row['firstPlaceName'] . '">';
                    echo '<h3>' . $row['tournamentName'] . '</h3>';
                    echo '<p>Tournament Start Date: ' . date('d/m/Y', strtotime($row['tournamentStartDate'])) . '</p>';
                    echo '<p>Tournament End Date: ' . date('d/m/Y', strtotime($row['tournamentEndDate'])) . '</p>';
                    echo '<h4>Winners:</h4>';
                    echo '<ul>';
                    echo '<li><strong>1st Place:</strong> ' . $row['firstPlaceName'] . '</li>';
                    echo '<li><strong>2nd Place:</strong> ' . $row['secondPlaceName'] . '</li>';
                    echo '<li><strong>3rd Place:</strong> ' . $row['thirdPlaceName'] . '</li>';
                    echo '</ul>';
                    echo '</li>';
                }
            } else {
                echo "No tournament results available.";
            }
            ?>
        </ul>
    </section>




    <section class="about" id="about">
      <h2>About Us</h2>
      <p>UTHM Official Eports Club</p>
      <div class="company-info">
        <h3>Our Story</h3>
        <p>Established since May 2023. Our main focus is to find and polish e-sports talent among students, hoping to level up together! Contact us at uthmesports@gmail.com</p>
      </div>
      
      <div class="team">
        <h3>Our Team</h3>
        <ul>
          <li>John Doe - Founder and CEO</li>
          <li>Jane Smith - Gear Specialist</li>
          <li>Mark Johnson - Customer Representative</li>
          <li>Sarah Brown - Operations Manager</li>
        </ul>
      </div>
    </section>
    
    <footer>
      <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="#">Home</a>
        </span>
      </div>
    </footer>
  </body>
</html>