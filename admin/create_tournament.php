<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Retrieve the latest tournamentID from the database
$sqlLatestTournamentID = "SELECT MAX(CAST(SUBSTRING(tournamentID, 3) AS UNSIGNED)) as latestTournamentID FROM tournaments";
$resultLatestTournamentID = $conn->query($sqlLatestTournamentID);

$latestTournamentID = 1; // Default value if there are no tournaments yet

if ($resultLatestTournamentID->num_rows > 0) {
    $row = $resultLatestTournamentID->fetch_assoc();
    $latestTournamentID = (int)$row['latestTournamentID'] + 1;
}

// Fetch all formIDs and formTitles for the dropdown
$sqlForms = "SELECT formID, formTitle FROM forms";
$resultForms = $conn->query($sqlForms);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $tournamentID = $_POST['tournamentID'];
    $formID = $_POST['formID'] ?? null;  // Use null if not provided
    $tournamentName = $_POST['tournamentName'];
    $tournamentStartDate = $_POST['tournamentStartDate'];
    $tournamentEndDate = $_POST['tournamentEndDate'];
    $postDesc = $_POST['postDesc'];
    $gameTitle = $_POST['gameTitle'];

    // Upload image file if provided
    $postPic = ''; // Default value
    if (isset($_FILES['postPic']) && $_FILES['postPic']['error'] == 0) {
        $targetDir = "../uploads/"; // Adjust the target directory
        $targetFile = $targetDir . basename($_FILES['postPic']['name']);
        move_uploaded_file($_FILES['postPic']['tmp_name'], $targetFile);
        $postPic = $targetFile;
    }

    // Insert data into the 'tournaments' table
    $sql = "INSERT INTO tournaments (tournamentID, formID, tournamentName, tournamentStartDate, tournamentEndDate, postDesc, postPic, gameTitle) 
            VALUES ('$tournamentID', " . ($formID ? "'$formID'" : "NULL") . ", '$tournamentName', '$tournamentStartDate', '$tournamentEndDate', '$postDesc', '$postPic', '$gameTitle')";

    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Tournament added successfully!"); window.location.href = "manage_tournament.php";</script>';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
    <title>Create Tournament - Admin</title>
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

    <main>
        <!-- Admin Tournament Form -->
        <form action="create_tournament.php" method="post" enctype="multipart/form-data" class="form">
            <h1>Create new Tournament</h1>
            
            <label for="tournamentID">Tournament ID:</label>
            <input type="text" name="tournamentID" value="TO<?= str_pad($latestTournamentID, 4, '0', STR_PAD_LEFT); ?>" required>

            <label for="formID">Form ID:</label> <br>
            <select name="formID" style="margin-bottom: 20px;">
                <option value="">None</option>
                <?php
                if ($resultForms->num_rows > 0) {
                    while($row = $resultForms->fetch_assoc()) {
                        echo '<option value="' . $row['formID'] . '">' . $row['formTitle'] . '</option>';
                    }
                }
                ?>
            </select>

            <label for="tournamentName">Tournament Name:</label>
            <input type="text" name="tournamentName" required>

            <label for="gameTitle">Game Title:</label>
            <input type="text" name="gameTitle" required>

            <label for="tournamentStartDate">Tournament Start Date:</label>
            <input type="date" name="tournamentStartDate" required>

            <label for="tournamentEndDate">Tournament End Date:</label>
            <input type="date" name="tournamentEndDate" required>

            <label for="postDesc">Post Description:</label><br>
            <textarea name="postDesc" rows="4" required></textarea><br>

            <label for="postPic">Post Picture:</label>
            <input type="file" accept=".jpg, .png" name="postPic">

            <button type="submit">Submit</button>
        </form>
    </main>

    <footer>
      <div>
        <span>&copy; 2023 All Rights Reserved</span>
        <span class="link">
            <a href="#">Home</a>
        </span>
      </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
