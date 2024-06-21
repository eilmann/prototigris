<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// JavaScript function to show error message and redirect
echo '<script>
        function showErrorAndRedirect() {
            alert("Registration form not found.");
            window.location.href = "../index.php"; // Redirect to the homepage
        }
      </script>';

// Check if a participant is logged in
if (!isParticipantLoggedIn()) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Check if the formID is provided in the URL
if (isset($_GET['formID'])) {
    $formID = $_GET['formID'];

    // Query to retrieve registration form details based on formID
    $formDetailsSql = "SELECT * FROM forms WHERE formID = '$formID'";
    $formDetailsResult = $conn->query($formDetailsSql);

    if ($formDetailsResult->num_rows > 0) {
        // Fetch the registration form details
        $formDetails = $formDetailsResult->fetch_assoc();

        // Count current registrations
        $currentRegistrationsSql = "SELECT COUNT(*) AS currentCount FROM tournament_registrations WHERE formID = '$formID'";
        $currentRegistrationsResult = $conn->query($currentRegistrationsSql);
        $currentCount = $currentRegistrationsResult->fetch_assoc()['currentCount'];

        // Get the max participants allowed
        $maxParticipants = $formDetails['max_participants'];

        // Display the registration form details
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" href="../img/tigris_logo.png" type="icon">
            <title>Tournament Registration</title>
            <link rel="stylesheet" href="../style.css">
        </head>
        <body>

        <header>
        <nav class="navbar">
          <div class="logo">
              <a href="../index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
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
            <li><a class="login-button" href="logout.php">Logout</a></li>
          </ul>
        </nav>
      </header>

            <main>
                <div class="form">
                    <h2 style = "margin-bottom: 10px">' . $formDetails['formTitle'] . '</h2>
                    <p>' . $formDetails['description'] . '</p>
                    <p>Registration Fee: RM' . $formDetails['registration_fee'] . '</p>';

                    // Check if registration is full
                    if ($currentCount >= $maxParticipants) {
                        echo '<p style="color:#ff7300;">Sorry, Registration is full for this tournament.</p>';
                    } else {
                        $participantID = $_SESSION['participantID'];
                        $participantName = $_SESSION['participantName'];

                        // Check if proof of payment is enabled
                        if ($formDetails['proof_of_payment_toggle'] == 1) {
                            echo '<p>Proof of Payment is required.</p>';
                            // Add HTML form field for proof of payment upload
                            echo '<form action="process_registration.php?formID=' . $formID . '" method="post" enctype="multipart/form-data">';
                            echo '<label for="participantID">Participant ID:</label>';
                            echo '<input type="text" name="participantID" placeholder="Enter your Participant ID" required>';
                            echo '<label for="participantName">Participant Name:</label>';
                            echo '<input type="text" name="participantName" value="' . $participantName . '" readonly>';
                            echo '<label for="proofOfPayment">Proof of Payment (PDF only):</label>';
                            echo '<input type="file" name="proofOfPayment" accept=".pdf" required>';
                            echo '<input type="hidden" name="MAX_FILE_SIZE" value="10485760">'; // Max file size (10 MB)

                            echo '<input type="checkbox" name="confirmInfo" id="confirmInfo" required>';
                            echo '<label for="confirmInfo">Confirm that the information is correct</label>';

                            echo '<input type="submit" name="submit" value="Submit Registration">';
                            echo '</form>';
                        } else {
                            echo '<form action="process_registration.php?formID=' . $formID . '" method="post">';
                            echo '<p>There is no registration fee for this tournament.</p>';
                            // Display form fields for participant input
                            echo '<label for="participantID">Participant ID:</label>';
                            echo '<input type="text" name="participantID" value="' . $participantID . '" readonly>';
                            echo '<label for="participantName">Participant Name:</label>';
                            echo '<input type="text" name="participantName" value="' . $participantName . '" readonly>';

                            echo '<input type="checkbox" name="confirmInfo" id="confirmInfo" required>';
                            echo '<label for="confirmInfo">Confirm that the information is correct</label>';

                            echo '<button type="submit">Submit Registration</button>';
                            echo '</form>';
                        }
                    }
                echo '</div>
            </main>

            <footer>
                <div>
                    <span>Copyright © 2023 All Rights Reserved</span>
                    <span class="link">
                        <a href="../index.php">Home</a>
                    </span>
                </div>
            </footer>

        </body>
        </html>';
    } else {
        // If the form is not found, display the error and redirect
        echo '<script>
                alert("Registration is not open yet.");
                window.location.href = "../index.php";
              </script>';
    }
} else {
    // If the formID is not provided in the URL, display the error and redirect
    echo '<script>
            alert("FormID not provided in the URL.");
            window.location.href = "../index.php";
          </script>';
}

// Close the database connection
$conn->close();
?>
