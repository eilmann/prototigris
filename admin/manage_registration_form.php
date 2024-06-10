<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Check if the form is submitted for deletion
if (isset($_GET['delete_formID'])) {
    $delete_formID = $_GET['delete_formID'];

    // Delete the record from the 'forms' table
    $delete_sql = "DELETE FROM forms WHERE formID='$delete_formID'";
    $conn->query($delete_sql);
}

// Query to retrieve data from the 'forms' table
$sql = "SELECT * FROM forms";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Manage Registration Forms - Admin</title>
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
        <section style="background-color: rgba(255, 255, 255, 0.1);">
            <h1 style="color: white;">Manage Registration Forms</h1>

            <a href="create_registration_form.php" class="create-post-button">Create New Form</a>

            <table>
                <tr>
                    <th>Form ID</th>
                    <th>Form Title</th>
                    <th>Description</th>
                    <th>Registration Fee</th>
                    <th>Action</th>
                    <th>View Data</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['formID'] . '</td>';
                        echo '<td>' . $row['formTitle'] . '</td>';
                        echo '<td>' . $row['description'] . '</td>';
                        echo '<td>' . $row['registration_fee'] . '</td>';
                        echo '<td>
                                <a href="edit_registration_form.php?edit_formID=' . $row['formID'] . '">Edit</a>
                                <a href="?delete_formID=' . $row['formID'] . '" onclick="return confirm(\'Are you sure?\')">Delete</a>
                              </td>';
                        echo '<td><a href="view_registration_data.php?formID=' . $row['formID'] . '">View Data</a></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No registration forms found.</td></tr>';
                }
                ?>
            </table>
        </section>
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
