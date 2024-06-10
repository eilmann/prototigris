<?php
include('../includes/session.php');
logoutParticipant();
header("Location:../client/index.php");
exit();
?>