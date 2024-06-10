<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Check if the form is submitted for deletion
if (isset($_GET['delete_tournamentID'])) {
    $delete_tournamentID = $_GET['delete_tournamentID'];

    // Delete the record from the 'tournaments' table
    $delete_sql = "DELETE FROM tournaments WHERE tournamentID='$delete_tournamentID'";
    $conn->query($delete_sql);
}

// Function to get tournament status
function getTournamentStatus($startDate, $endDate) {
    $currentDate = date("Y-m-d");
    if ($currentDate < $startDate) {
        return "Upcoming";
    } elseif ($currentDate <= $endDate) {
        return "Ongoing";
    } else {
        return "Ended";
    }
}

// Query to retrieve data from the 'tournaments' table
$sql = "SELECT * FROM tournaments";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Manage Tournaments - Admin</title>
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

<section style= "background-color: rgba(255, 255, 255, 0.1);">
    <h1 style= "color: white;">Manage Tournaments</h1>

    <a href="create_tournament.php" class="create-post-button">Create New Tournament</a>

    <table>
        <tr>
            <th>Tournament ID</th>
            <th>Tournament Name</th>
            <th>Tournament Start Date</th>
            <th>Tournament End Date</th>
            <th>Status</th>
            <th>Form ID</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['tournamentID'] . '</td>';
                echo '<td>' . $row['tournamentName'] . '</td>';
                echo '<td>' . $row['tournamentStartDate'] . '</td>';
                echo '<td>' . $row['tournamentEndDate'] . '</td>';
                // Get tournament status
                $status = getTournamentStatus($row['tournamentStartDate'], $row['tournamentEndDate']);
                echo '<td>' . $status . '</td>';
                echo '<td>' . $row['formID'] . '</td>';
                echo '<td>
                        <a href="edit_tournament.php?edit_tournamentID=' . $row['tournamentID'] . '">Edit</a>
                        <a href="?delete_tournamentID=' . $row['tournamentID'] . '" onclick="return confirm(\'Are you sure?\')">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8">No tournaments found.</td></tr>';
        }
        ?>
    </table>
</section>

<footer>
    <div>
        <span>&copy; 2023 All Rights Reserved</span>
        <span class="link">
            <a href="dashboard.php">Home</a>
        </span>
    </div>
</footer>

<script src="../js/script.js"></script>
</body>
</html>
