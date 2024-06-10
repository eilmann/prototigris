<?php
include('../includes/db.php');
include('../includes/session.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $adminID = $_POST['adminID'];
    $adminPW = $_POST['adminPW'];

    // Validate login credentials
    $sql = "SELECT * FROM admins WHERE adminID='$adminID'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Fetch the user data
        $row = $result->fetch_assoc();

            loginAdmin($row['adminID'], $row['adminName']);
            header("Location: dashboard.php"); // Redirect to admin homepage
            exit();
        
    } else {
        // Invalid admin ID or password
        $_SESSION['loginError'] = "Invalid admin ID or password";
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
    <script>
        // Function to display login error as popup window
        function displayLoginError(errorMsg) {
            alert(errorMsg);
        }
    </script>
</head>

    <body style = "background-image: url(../img/tigris_background.jpg)">
    <header>
      <nav class="navbar">
        <div class="logo">
            <a href="../client/index.php"><img src="../img/tigris_logo.png" alt="logo">UTHM TIGRIS E-SPORTS WEBSITE</a>
        </div>
      </nav>
    </header>

    <main>
        <form action="login.php" method="post" class="form">
        <h3>Admin Login Form</h3>
            <label for="adminID">Admin ID:</label>
            <input type="text" name="adminID" required>

            <label for="adminPW">Password:</label>
            <input type="password" name="adminPW" required>

            <button type="submit">Login</button>

            <p>Not an admin? <a href="../client/index.php">Go back to main page</a></p>

        </form>

        <?php
        // Check if login error message is set in session and display it using JavaScript popup
        if (isset($_SESSION['loginError'])) {
            echo '<script>displayLoginError("' . $_SESSION['loginError'] . '");</script>';
            // Clear the session variable after displaying the error message
            unset($_SESSION['loginError']);
        }
        ?>
    </main>

    <footer>
      <div>
        <span>Copyright © 2023 All Rights Reserved</span>
        <span class="link">
            <a href="../client/index.php">Home</a>
        </span>
      </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
