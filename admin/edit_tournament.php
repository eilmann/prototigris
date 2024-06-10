<?php
// Include the database connection file
include('../includes/db.php');
include('../includes/session.php');

// Check if the form is submitted for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $tournamentID = $_POST['tournamentID'];
    $formID = $_POST['formID'] ?? null;  // Use 'none' if not provided
    $tournamentName = $_POST['tournamentName'];
    $tournamentStartDate = $_POST['tournamentStartDate'];
    $tournamentEndDate = $_POST['tournamentEndDate'];
    $postDesc = $_POST['postDesc'];
    $gameTitle = $_POST['gameTitle'];

    // Initialize the SQL query with the fields to be updated
    $update_sql = "UPDATE tournaments SET 
                    formID='$formID', 
                    tournamentName='$tournamentName', 
                    tournamentStartDate='$tournamentStartDate', 
                    tournamentEndDate='$tournamentEndDate', 
                    postDesc='$postDesc',
                    gameTitle='$gameTitle'";

    // Upload image file if provided
    if (isset($_FILES['postPic']) && $_FILES['postPic']['error'] == 0) {
        $targetDir = "../uploads/"; // Adjust the target directory
        $targetFile = $targetDir . basename($_FILES['postPic']['name']);
        move_uploaded_file($_FILES['postPic']['tmp_name'], $targetFile);
        $postPic = $targetFile;

        // Append postPic to the SQL update query
        $update_sql .= ", postPic='$postPic'";
    }

    // Finalize the SQL query with the condition
    $update_sql .= " WHERE tournamentID='$tournamentID'";

    if ($conn->query($update_sql) === TRUE) {
        echo '<script>alert("Tournament updated successfully!"); window.location.href = "manage_tournament.php";</script>';
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Check if the edit_tournamentID is set in the URL
if (isset($_GET['edit_tournamentID'])) {
    $edit_tournamentID = $_GET['edit_tournamentID'];

    // Query to retrieve data for the selected tournament
    $edit_sql = "SELECT * FROM tournaments WHERE tournamentID='$edit_tournamentID'";
    $edit_result = $conn->query($edit_sql);

    if ($edit_result->num_rows == 1) {
        $edit_row = $edit_result->fetch_assoc();
    } else {
        echo "Tournament not found!";
        exit();
    }
} else {
    // If edit_tournamentID is not set, redirect to manage_tournament.php
    header("Location: manage_tournament.php");
    exit();
}

// Fetch all formIDs and formTitles for the dropdown
$sqlForms = "SELECT formID, formTitle FROM forms";
$resultForms = $conn->query($sqlForms);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Edit Tournament - Admin</title>
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
        <form action="" method="post" enctype="multipart/form-data" class="form">
            <h1 style="margin-bottom: 20px;">Edit Tournament</h1>
            <!-- Add form fields for tournament input (tournamentID, formID, tournamentName, tournamentStartDate, tournamentEndDate, postDesc, postPic) -->
            <input type="hidden" name="tournamentID" value="<?php echo $edit_row['tournamentID']; ?>">

            <label for="formID">Form ID:</label> <br>
            <select name="formID"  style="margin-bottom: 20px;">
                <option value="">None</option>
                <?php
                if ($resultForms->num_rows > 0) {
                    while($row = $resultForms->fetch_assoc()) {
                        $selected = $row['formID'] == $edit_row['formID'] ? 'selected' : '';
                        echo '<option value="' . $row['formID'] . '" ' . $selected . '>' . $row['formTitle'] . '</option>';
                    }
                }
                ?>
            </select> <br>

            <label for="tournamentName">Tournament Name:</label>
            <input type="text" name="tournamentName" value="<?php echo $edit_row['tournamentName']; ?>" required>

            <label for="gameTitle">Game Title:</label>
            <input type="text" name="gameTitle" value="<?php echo $edit_row['gameTitle']; ?>" required>

            <label for="tournamentStartDate">Tournament Start Date:</label>
            <input type="date" name="tournamentStartDate" value="<?php echo $edit_row['tournamentStartDate']; ?>" required>

            <label for="tournamentEndDate">Tournament End Date:</label>
            <input type="date" name="tournamentEndDate" value="<?php echo $edit_row['tournamentEndDate']; ?>" required>

            <label for="postDesc">Post Description:</label><br>
            <textarea name="postDesc" rows="4" style="margin-bottom: 10px;" required><?php echo $edit_row['postDesc']; ?></textarea><br>

            <label for="postPic">Current Post Picture:</label><br>
            <img src="<?php echo $edit_row['postPic']; ?>" alt="Current Post Picture" style="max-width: 300px; margin-bottom: 10px;"><br>

            <label for="newPostPic">New Post Picture:</label>
            <input type="file" accept=".jpg, .png" name="postPic">

            <button type="submit">Update</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 UTHM Tigris E-Sports Website</p>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
