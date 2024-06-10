<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Check if formID is set in the URL
if (isset($_GET['formID'])) {
    $formID = $_GET['formID'];

    // Query to check if proof_of_payment_toggle is 0
    $check_payment_toggle_sql = "SELECT proof_of_payment_toggle FROM forms WHERE formID='$formID'";
    $toggle_result = $conn->query($check_payment_toggle_sql);
    if ($toggle_result->num_rows > 0) {
        $toggle_row = $toggle_result->fetch_assoc();
        if ($toggle_row['proof_of_payment_toggle'] == 0) {
            // Automatically approve registrations for free tournaments
            $auto_approve_sql = "UPDATE tournament_registrations SET status='Approved' WHERE formID='$formID' AND status='Pending'";
            $conn->query($auto_approve_sql);
        }
    }

    // Check if the admin wants to approve or deny a registration
    if (isset($_GET['action']) && isset($_GET['registrationID'])) {
        $action = $_GET['action'];
        $registrationID = $_GET['registrationID'];
        $status = '';

        if ($action == 'approve') {
            $status = 'Approved';
        } elseif ($action == 'deny') {
            $status = 'Denied';
        }

        if ($status) {
            $update_sql = "UPDATE tournament_registrations SET status='$status' WHERE registrationID='$registrationID'";
            if ($conn->query($update_sql) === TRUE) {
                echo "<script>alert('Registration status changed to $status successfully!');</script>";
            } else {
                echo "<script>alert('Error updating status: " . $conn->error . "');</script>";
            }
        }
    }

    // Query to retrieve data from the 'tournament_registrations' table based on formID
    $sql = "SELECT tournament_registrations.registrationID, participants.participantID, participants.participantName, participants.participantEmail, tournament_registrations.proof_of_payment_path, tournament_registrations.status
            FROM participants
            JOIN tournament_registrations ON participants.participantID = tournament_registrations.participantID
            WHERE tournament_registrations.formID='$formID'";
    $result = $conn->query($sql);
} else {
    // Redirect to the manage_registration_forms.php page if formID is not set
    header("Location: manage_registration_forms.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>View Registration Data - Admin</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .action-button {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin: 0 5px;
        }
        .approve {
            background-color: green;
        }
        .deny {
            background-color: red;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-denied {
            color: red;
            font-weight: bold;
        }
    </style>
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

    <section style="background-color: rgba(255, 255, 255, 0.1);">
        <h1 style="color: white;">View Registration Data</h1>

        <a href="manage_registration_form.php" class="create-post-button">Back to Manage Forms</a>

        <table>
            <tr>
                <th>Participant ID</th>
                <th>Participant Name</th>
                <th>Email</th>
                <th>Proof of Payment</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['participantID'] . '</td>';
                    echo '<td>' . $row['participantName'] . '</td>';
                    echo '<td>' . $row['participantEmail'] . '</td>';
                    echo '<td>';
                    if (!empty($row['proof_of_payment_path'])) {
                        echo '<a href="../' . $row['proof_of_payment_path'] . '" target="_blank">View Proof</a>';
                    } else {
                        echo 'N/A';
                    }
                    echo '</td>';
                    echo '<td>';
                    if ($row['status'] == 'Pending') {
                        echo '<a class="action-button approve" href="?formID=' . $formID . '&action=approve&registrationID=' . $row['registrationID'] . '">Approve</a>';
                        echo '<a class="action-button deny" href="?formID=' . $formID . '&action=deny&registrationID=' . $row['registrationID'] . '">Deny</a>';
                    } else {
                        echo '<span class="status-' . strtolower($row['status']) . '">' . $row['status'] . '</span>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">No registration data found.</td></tr>';
            }
            ?>
        </table>
    </section>

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
