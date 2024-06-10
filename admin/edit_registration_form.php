<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Check if the form is submitted for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formID = $_POST['formID'];
    $formTitle = $_POST['formTitle'];
    $description = $_POST['description'];
    $registrationFee = $_POST['registration_fee'];
    $proofOfPaymentToggle = $_POST['proof_of_payment_toggle'];
    $maxParticipants = $_POST['max_participants'];
    $tournamentID = $_POST['tournamentID'];

    // Update the record in the 'forms' table
    $update_sql = "UPDATE forms SET 
                    formTitle='$formTitle', 
                    description='$description', 
                    registration_fee='$registrationFee', 
                    proof_of_payment_toggle='$proofOfPaymentToggle', 
                    max_participants='$maxParticipants', 
                    tournamentID='$tournamentID' 
                    WHERE formID='$formID'";

    if ($conn->query($update_sql) === TRUE) {
        // Update tournamentID in the tournaments table
        $updateTournamentID_sql = "UPDATE tournaments SET formID='$formID' WHERE tournamentID='$tournamentID'";
        $conn->query($updateTournamentID_sql);

        // Redirect to the manage_registration_form.php page after updating
        header("Location: manage_registration_form.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Check if the edit_formID is set in the URL
if (isset($_GET['edit_formID'])) {
    $edit_formID = $_GET['edit_formID'];

    // Query to retrieve data for the selected registration form
    $edit_sql = "SELECT * FROM forms WHERE formID='$edit_formID'";
    $edit_result = $conn->query($edit_sql);

    if ($edit_result->num_rows == 1) {
        $edit_row = $edit_result->fetch_assoc();
    } else {
        echo "Registration form not found!";
        exit();
    }
} else {
    // If edit_formID is not set, redirect to manage_registration_form.php
    header("Location: manage_registration_form.php");
    exit();
}

// Retrieve tournaments list from the database
$tournaments_sql = "SELECT * FROM tournaments";
$tournaments_result = $conn->query($tournaments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Edit Registration Form - Admin</title>
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
        <form action="" method="post" class="form">
            <h1>Edit Registration Form</h1>
            <!-- Add form fields for form input (formID, formTitle, description, registration_fee, proof_of_payment_toggle) -->
            <input type="hidden" name="formID" value="<?php echo $edit_row['formID']; ?>">

            <label for="formTitle">Form Title:</label>
            <input type="text" name="formTitle" value="<?php echo $edit_row['formTitle']; ?>" required>

            <label for="description">Description:</label><br>
            <textarea name="description" rows="4" required><?php echo $edit_row['description']; ?></textarea><br>

            <label for="registration_fee">Registration Fee:</label>
            <input type="text" name="registration_fee" value="<?php echo $edit_row['registration_fee']; ?>" required>

            <label for="proof_of_payment_toggle">Proof of Payment Toggle:</label><br>
            <select name="proof_of_payment_toggle" required>
                <option value="0" <?php echo ($edit_row['proof_of_payment_toggle'] == 0) ? 'selected' : ''; ?>>Disabled</option>
                <option value="1" <?php echo ($edit_row['proof_of_payment_toggle'] == 1) ? 'selected' : ''; ?>>Enabled</option>
            </select><br><br>

            <!-- Add max_participants and tournamentID fields -->
            <label for="max_participants">Max Participants:</label>
            <input type="text" name="max_participants" value="<?php echo $edit_row['max_participants']; ?>" required>

            <label for="tournamentID">Tournament:</label>
            <select name="tournamentID" required>
                <?php
                if ($tournaments_result->num_rows > 0) {
                    while ($tournament_row = $tournaments_result->fetch_assoc()) {
                        $selected = ($tournament_row['tournamentID'] == $edit_row['tournamentID']) ? 'selected' : '';
                        echo '<option value="' . $tournament_row['tournamentID'] . '" ' . $selected . '>' . $tournament_row['tournamentName'] . '</option>';
                    }
                } else {
                    echo '<option value="" disabled>No tournaments available</option>';
                }
                ?>
            </select><br>

            <button type="submit">Update Registration Form</button>
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
