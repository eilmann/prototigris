<?php
include('../includes/db.php');
include('../includes/session.php');

// Fetch all form titles and IDs
$sql = "SELECT formID, formTitle FROM forms";
$result = $conn->query($sql);

$forms = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $forms[$row['formID']] = $row['formTitle'];
    }
}

// Handle form submission (deleting bracket)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteBracket']) && isset($_POST['bracketID'])) {
    $bracketID = $_POST['bracketID'];

    // Delete related schedules
    $sql = "DELETE FROM schedules WHERE bracketID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $bracketID);
    $stmt->execute();
    $stmt->close();

    // Delete bracket
    $sql = "DELETE FROM brackets WHERE bracketID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $bracketID);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid resubmission on refresh
    header("Location: manage_bracket.php");
    exit();
}


// Fetch brackets and related schedules for the selected form if formID is provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['formID'])) {
    $formID = $_POST['formID'];

    // Fetch brackets for the selected form
    $sql = "SELECT * FROM brackets WHERE formID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $formID);
    $stmt->execute();
    $result = $stmt->get_result();

    $brackets = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Fetch related schedules for each bracket
            $bracketID = $row['bracketID'];
            $sql = "SELECT * FROM schedules WHERE bracketID = ?";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("s", $bracketID);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            $schedules = [];
            if ($result2->num_rows > 0) {
                while ($scheduleRow = $result2->fetch_assoc()) {
                    $schedules[] = $scheduleRow;
                }
            }
            $stmt2->close();

            $row['schedules'] = $schedules;
            $brackets[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Manage Tournament Brackets</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .bracket-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .bracket {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .bracket h2 {
            margin-top: 0;
        }
        .bracket p {
            margin: 5px 0;
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

<section id="manage-bracket-section">
    <h1 style="color: white; margin-bottom: 20px;">Manage Tournament Brackets</h1>
    <form method="post">
        <label for="form-select" style="color: white;">Select Form:</label>
        <select id="form-select" name="formID">
            <?php foreach ($forms as $formID => $formTitle): ?>
                <option value="<?php echo $formID; ?>"><?php echo $formTitle; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Brackets</button>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['formID']) && !empty($brackets)): ?>
        <div class="bracket-container">
            <?php foreach ($brackets as $bracket): ?>
                <div class="bracket">
                    <h2>Bracket ID: <?php echo $bracket['bracketID']; ?></h2>
                    <p>Elimination Type: <?php echo $bracket['eliminationType']; ?></p>
                    <h3>Matches</h3>
                    <?php foreach ($bracket['schedules'] as $schedule): ?>
                        <p><?php echo $schedule['match_label']; ?> - <?php echo $schedule['match_date']; ?> <?php echo $schedule['match_time']; ?></p>
                    <?php endforeach; ?>
                    <form method="post">
                        <input type="hidden" name="bracketID" value="<?php echo $bracket['bracketID']; ?>">
                        <button type="submit" name="deleteBracket">Delete Bracket</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['formID']) && empty($brackets)): ?>
        <p style="color: red;">No brackets found for the selected form.</p>
    <?php endif; ?>
</section>

<footer>
    <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="dashboard.php">Home</a>
        </span>
    </div>
</footer>

</body>
</html>
