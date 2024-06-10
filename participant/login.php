<?php
include('../includes/db.php');
include('../includes/session.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $participantID = $_POST['participantID'];
    $participantPW = $_POST['participantPW'];

    // Validate login credentials
    $sql = "SELECT * FROM participants WHERE participantID='$participantID'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Fetch the user data
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($participantPW, $row['participantPW'])) {
            // Successful login
            loginParticipant($row['participantID'], $row['participantName']);
            header("Location: ../client/index.php"); // Redirect to participant homepage
            exit();
        } else {
            // Invalid password
            $_SESSION['loginError'] = "Invalid participant ID or password";
            header("Location: login.php"); // Redirect back to the login page
            exit();
        }
    } else {
        // Invalid participant ID
        $_SESSION['loginError'] = "Invalid participant ID or password";
        header("Location: login.php"); // Redirect back to the login page
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/tigris_logo.png" type="icon">
    <title>Participant Login</title>
    <link rel="stylesheet" href="../style.css">
    <script>
        // Function to display login error as popup window
        function displayLoginError(errorMsg) {
            alert(errorMsg);
        }
    </script>
</head>

<body style="background-image: url(../img/tigris_background.jpg)">
<header>
    <nav class="navbar">
        <div class="logo">
            <a href="../client/index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
    </nav>
</header>

<section class="form">
    <h3>Login Form</h3>
    <form action="login.php" method="post" class="loginForm">
        <label for="participantID">Participant ID:</label>
        <input type="text" name="participantID" required>

        <label for="participantPW">Password:</label>
        <input type="password" name="participantPW" required>

        <button type="submit">Login</button>

        <p>Don't have an account? <a href="register.php">Register as Participant</a></p>
        <p style="margin-bottom: 20px;">Admin? <a href="../admin/login.php">Admin Login</a></p>
    </form>

    <?php
    // Check if login error message is set in session and display it using JavaScript popup
    if (isset($_SESSION['loginError'])) {
        echo '<script>displayLoginError("' . $_SESSION['loginError'] . '");</script>';
        // Clear the session variable after displaying the error message
        unset($_SESSION['loginError']);
    }
    ?>
</section>

<footer>
    <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="../client/index.php">Home</a>
        </span>
    </div>
</footer>

</body>
</html>
