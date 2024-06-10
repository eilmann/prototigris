<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Retrieve the latest formID from the database
$sqlLatestFormID = "SELECT MAX(CAST(SUBSTRING(formID, 3) AS UNSIGNED)) AS latestFormID FROM forms";
$resultLatestFormID = $conn->query($sqlLatestFormID);

$latestFormID = 1; // Default value if there are no forms yet

if ($resultLatestFormID->num_rows > 0) {
    $row = $resultLatestFormID->fetch_assoc();
    $latestFormID = (int)$row['latestFormID'] + 1;
}

// Default value for registration_fee
$defaultRegistrationFee = 0;

// Retrieve tournament IDs from the database
$sqlTournamentIDs = "SELECT * FROM tournaments";
$resultTournamentIDs = $conn->query($sqlTournamentIDs);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $formID = $_POST['formID'];
    $formTitle = $_POST['formTitle'];
    $description = $_POST['description'];
    $registration_fee = isset($_POST['registration_fee']) ? $_POST['registration_fee'] : $defaultRegistrationFee;
    $proof_of_payment_toggle = isset($_POST['proof_of_payment_toggle']) ? 1 : 0;
    $max_participants = $_POST['max_participants'];
    $tournamentID = $_POST['tournamentID'];

    // Insert data into the 'forms' table
    $insert_sql = "INSERT INTO forms (`formID`, `formTitle`, `description`, `registration_fee`, `proof_of_payment_toggle`, `max_participants`, `tournamentID`) 
                   VALUES ('$formID', '$formTitle', '$description', $registration_fee, $proof_of_payment_toggle, $max_participants, '$tournamentID')";

    if ($conn->query($insert_sql) === TRUE) {
        // Update tournamentID in the tournaments table
        $updateTournamentID_sql = "UPDATE tournaments SET formID='$formID' WHERE tournamentID='$tournamentID'";
        $conn->query($updateTournamentID_sql);

        echo '<script>alert("Registration form added successfully!"); window.location.href = "manage_registration_form.php";</script>';
    } else {
        echo '<script>alert("Error: ' . $insert_sql . '<br>' . $conn->error . '");</script>';
    }
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
    <title>Create Registration Form - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="background: url(../img/tigris_background.jpg)">

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

    <main class="admin-main">
        <!-- Admin Registration Form -->
        <form action="create_registration_form.php" method="post" class="form">
            <h1>Create New Registration Form</h1>
            <!-- Add form fields for registration form input (formID, formTitle, description, registration_fee, proof_of_payment_toggle, max_participants, tournamentID) -->
            <label for="formID">Form ID:</label>
            <input type="text" name="formID" value="FI<?= str_pad($latestFormID, 4, '0', STR_PAD_LEFT); ?>" required>

            <label for="formTitle">Form Title:</label>
            <input type="text" name="formTitle" required>

            <label for="description">Description:</label><br>
            <textarea name="description" rows="4"></textarea><br>

            <label for="registration_fee">Registration Fee (RM):</label>
            <input type="number" name="registration_fee" min="0" value="<?= $defaultRegistrationFee ?>">

            <label for="proof_of_payment_toggle">Require Proof of Payment:</label>
            <input type="checkbox" name="proof_of_payment_toggle"><br>

            <label for="max_participants">Max Participants:</label>
            <input type="number" name="max_participants" min="1" required>

            <label for="tournamentID">Tournament Name:</label><br>
            <select name="tournamentID" required>
                <?php
                // Populate dropdown menu with tournament IDs
                if ($resultTournamentIDs->num_rows > 0) {
                    while ($row = $resultTournamentIDs->fetch_assoc()) {
                        echo '<option value="' . $row['tournamentID'] . '">' . $row['tournamentName'] . '</option>';
                    }
                } else {
                    echo '<option value="" disabled>No tournaments found</option>';
                }
                ?>
            </select><br><br>

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
</body>
</html>