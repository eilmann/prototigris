<?php
include('../includes/session.php');
logoutAdmin(); // Call the logout function
header("Location: login.php"); // Redirect to the login page after logout
exit();
?>