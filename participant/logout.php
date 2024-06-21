<?php
include('../includes/session.php');
logoutParticipant();
header("Location:../index.php");
exit();
?>